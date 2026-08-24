<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Robust service to parse Spanish & International identity documents
 * (DNI 3.0/4.0 Anverso & Reverso, NIE, CIF, Passports, LATAM IDs).
 * Extracts structured data (Full Name, 1st Surname, 2nd Surname, Given Name, Tax ID, Address, CP, City, Expiry).
 */
class IdentityCardParserService
{
    public function __construct(
        private readonly SpanishTaxIdValidator $spanishValidator,
        private readonly LatinAmericanTaxIdValidator $latamValidator,
    ) {}

    /**
     * Parses an uploaded identity card and extracts structured data.
     *
     * @return array{
     *   success: bool,
     *   side: string,
     *   document_type: string,
     *   tax_id: ?string,
     *   tax_id_country: string,
     *   tax_id_valid: bool,
     *   tax_id_kind: string,
     *   party_type: string,
     *   full_name: ?string,
     *   first_name: ?string,
     *   last_name: ?string,
     *   address: ?string,
     *   postal_code: ?string,
     *   city: ?string,
     *   country: string,
     *   expiry_date: ?string,
     *   scan_token: string,
     *   filename: string,
     *   stored_path: string,
     *   raw_ocr: string,
     * }
     */
    public function parse(UploadedFile $file, ?string $clientOcrText = null, string $side = 'auto'): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
        $scanToken = (string) Str::uuid();
        $storedPath = "documents/temp_scans/{$scanToken}.{$extension}";
        $diskName = config('filesystems.documents_disk', 'local');

        // Store temporarily in private storage disk (local or cloud)
        Storage::disk($diskName)->putFileAs('documents/temp_scans', $file, "{$scanToken}.{$extension}");

        $serverText = $this->extractRawText($file);
        $combinedText = trim($clientOcrText ? "{$clientOcrText}\n{$serverText}" : $serverText);

        $parsed = $this->parseText($combinedText, $file->getClientOriginalName(), $side);

        $parsed['scan_token'] = $scanToken;
        $parsed['filename'] = $file->getClientOriginalName();
        $parsed['stored_path'] = $storedPath;
        $parsed['raw_ocr'] = mb_substr($combinedText, 0, 500);

        return $parsed;
    }

    /**
     * Extracts text content from a file (PDF text streams, MRZ strings, metadata, etc.).
     */
    public function extractRawText(UploadedFile $file): string
    {
        $mime = $file->getMimeType() ?: '';
        $path = $file->getRealPath();

        if (str_contains($mime, 'pdf')) {
            $content = file_get_contents($path);
            if ($content === false) {
                return '';
            }

            // Extract plain text from uncompressed PDF streams
            $text = '';
            if (preg_match_all('/(?:ET|BT)\s*\[?\((.*?)\)\]?\s*(?:TJ|Tj|ET)/s', $content, $matches)) {
                $text = implode(' ', $matches[1]);
            }
            if (empty(trim($text))) {
                $utf8 = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
                $clean = preg_replace('/[^\p{L}\p{N}\p{P}\p{Z}\n\r<]/u', ' ', $utf8) ?? '';
                $text = preg_replace('/[ \t]+/', ' ', $clean) ?? '';
            }

            return $text;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return '';
        }

        $utf8 = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        $clean = preg_replace('/[^\p{L}\p{N}\p{P}\p{Z}\n\r<]/u', ' ', $utf8) ?? '';

        return preg_replace('/[ \t]+/', ' ', $clean) ?? '';
    }

    /**
     * Parses raw extracted text into structured identity attributes.
     */
    public function parseText(string $text, string $originalFilename = '', string $requestedSide = 'auto'): array
    {
        // 1. Check Machine Readable Zone (MRZ) TD1 / TD3
        $mrzResult = $this->parseMrz($text);

        // Clean & normalize text for analysis
        $cleanText = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = array_values(array_filter(array_map('trim', explode("\n", $cleanText))));

        // 2. Extract Spanish/Latam Tax ID using precision algorithms
        $taxInfo = $this->extractTaxId($cleanText, $mrzResult);
        $taxId = $taxInfo['tax_id'];
        $taxIdCountry = $taxInfo['country'];
        $taxIdKind = $taxInfo['kind'];
        $taxIdValid = $taxInfo['valid'];
        $docType = $taxInfo['doc_type'];
        $partyType = $taxInfo['party_type'];

        // 3. Extract Names (First Surname, Second Surname, Given Name)
        $nameInfo = $this->extractNames($lines, $cleanText, $mrzResult);
        $firstName = $nameInfo['first_name'];
        $lastName = $nameInfo['last_name'];
        $fullName = $nameInfo['full_name'];

        // 4. Extract Address / Domicilio (typically on the back / reverso)
        $address = null;
        if (preg_match('/(?:DOMICILIO|DIRECCI[ÓO]N|CALLE|AVDA|AVENIDA)[\s:\.]*([A-Z0-9ÁÉÍÓÚÑa-záéíóúñ\s,\.\/ºª\-]+?)(?=\s*(?:MUNICIPIO|LOCALIDAD|CIUDAD|CP|C\.P\.|PROVINCIA|PA[IÍ]S|IDESP|\n|$))/iu', $cleanText, $m)) {
            $address = trim(preg_replace('/\s+/', ' ', $m[1]));
        }

        // 5. Extract City / Municipality
        $city = null;
        if (preg_match('/(?:MUNICIPIO|LOCALIDAD|CIUDAD|POBLACI[ÓO]N)[\s:\.]*([A-ZÁÉÍÓÚÑa-záéíóúñ\s]+?)(?=\s*(?:PROVINCIA|CP|C\.P\.|PA[IÍ]S|VALIDEZ|CADUCIDAD|IDESP|\n|$))/iu', $cleanText, $m)) {
            $city = ucwords(mb_strtolower(trim($m[1])));
        }

        // 6. Extract Postal code
        $postalCode = null;
        if (preg_match('/\b(0[1-9]|[1-4]\d|5[0-2])\d{3}\b/', $cleanText, $m)) {
            $postalCode = $m[0];
        }

        // 7. Expiration date
        $expiryDate = $mrzResult['expiry_date'] ?? null;
        if ($expiryDate === null && preg_match('/(?:VALIDEZ|CADUCIDAD|VENCE|EXPIR(?:Y|ES)?)[\s:\.]*(\d{2}[\/\-\s\.]\d{2}[\/\-\s\.]\d{4})/iu', $cleanText, $m)) {
            $expiryDate = str_replace(['/', '.', ' '], '-', $m[1]);
        }

        // 8. Detect side (Front vs Back)
        $detectedSide = $requestedSide !== 'auto' ? $requestedSide : 'unknown';
        if ($detectedSide === 'unknown') {
            $upper = strtoupper($cleanText);
            if ($mrzResult !== null || $address !== null || $city !== null || str_contains($upper, 'DOMICILIO') || str_contains($upper, 'HIJO DE')) {
                $detectedSide = 'back';
            } elseif ($firstName !== null || $lastName !== null || str_contains($upper, 'APELLIDO') || str_contains($upper, 'NACIONALIDAD') || str_contains($upper, 'FECHA DE NACIMIENTO')) {
                $detectedSide = 'front';
            }
        }

        return [
            'success' => $taxId !== null || $fullName !== null || $address !== null,
            'side' => $detectedSide,
            'document_type' => $docType,
            'tax_id' => $taxId,
            'tax_id_country' => $taxIdCountry,
            'tax_id_valid' => $taxIdValid,
            'tax_id_kind' => $taxIdKind,
            'party_type' => $partyType,
            'full_name' => $fullName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'address' => $address,
            'postal_code' => $postalCode,
            'city' => $city,
            'country' => $taxIdCountry,
            'expiry_date' => $expiryDate,
        ];
    }

    /**
     * Extracts and validates Tax ID (NIF / NIE / CIF / LATAM) from text or MRZ.
     */
    private function extractTaxId(string $text, ?array $mrzResult): array
    {
        if ($mrzResult && ! empty($mrzResult['tax_id'])) {
            return [
                'tax_id' => $mrzResult['tax_id'],
                'country' => $mrzResult['tax_id_country'],
                'kind' => $mrzResult['tax_id_kind'],
                'valid' => $mrzResult['tax_id_valid'],
                'doc_type' => $mrzResult['document_type'],
                'party_type' => $mrzResult['party_type'],
            ];
        }

        $upper = strtoupper($text);

        // 1. Explicit search with DNI/NIE/NUM labels and formatted dots/hyphens: e.g. "NUM: 12.345.678-Z" or "DNI 12345678Z"
        if (preg_match_all('/(?:DNI|NIF|NIE|CIF|NUM|N[ºo]|DOCUMENTO|ID|IDESP)?[\s:\.\/ºª#\-]*([XYZ\d][\d\s\.\-]{6,12}[A-Z0-9])/i', $upper, $matches)) {
            foreach ($matches[1] as $raw) {
                $clean = strtoupper(preg_replace('/[\s\.\-_]/', '', $raw));
                if ($this->spanishValidator->isValid($clean)) {
                    $isNie = in_array($clean[0], ['X', 'Y', 'Z'], true);
                    $isCif = in_array($clean[0], ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'N', 'P', 'Q', 'R', 'S', 'U', 'V', 'W'], true);

                    return [
                        'tax_id' => $clean,
                        'country' => 'ES',
                        'kind' => $isNie ? 'nie' : ($isCif ? 'cif' : 'nif'),
                        'valid' => true,
                        'doc_type' => $isNie ? 'nie' : ($isCif ? 'cif' : 'dni'),
                        'party_type' => $isCif ? 'sociedad' : 'particular',
                    ];
                }
            }
        }

        // 2. Global scan for any valid Spanish NIF / NIE (standard format)
        if (preg_match_all('/([XYZ]\d{7}[A-Z]|\d{8}[A-Z])/i', $upper, $matches)) {
            foreach ($matches[1] as $candidate) {
                $clean = strtoupper($candidate);
                if ($this->spanishValidator->isValid($clean)) {
                    $isNie = in_array($clean[0], ['X', 'Y', 'Z'], true);

                    return [
                        'tax_id' => $clean,
                        'country' => 'ES',
                        'kind' => $isNie ? 'nie' : 'nif',
                        'valid' => true,
                        'doc_type' => $isNie ? 'nie' : 'dni',
                        'party_type' => 'particular',
                    ];
                }
            }
        }

        // 3. Scan with common OCR digit replacements (e.g. O->0, I/l->1, B->8 in number parts)
        if (preg_match_all('/(?:[XYZ\dOIlB][\s\.\-_]*){8,9}[A-Z]/i', $upper, $matches)) {
            foreach ($matches[0] as $raw) {
                $clean = strtoupper(preg_replace('/[\s\.\-_]/', '', $raw));
                if (strlen($clean) === 9) {
                    $numPart = substr($clean, 1, 7);
                    $firstChar = $clean[0];
                    $lastChar = $clean[8];

                    $fixedNum = strtr($numPart, ['O' => '0', 'I' => '1', 'L' => '1', 'B' => '8', 'S' => '5']);
                    $fixedFirst = in_array($firstChar, ['X', 'Y', 'Z'], true) ? $firstChar : strtr($firstChar, ['O' => '0', 'I' => '1', 'L' => '1', 'B' => '8', 'S' => '5']);
                    $candidate = "{$fixedFirst}{$fixedNum}{$lastChar}";

                    if ($this->spanishValidator->isValid($candidate)) {
                        $isNie = in_array($candidate[0], ['X', 'Y', 'Z'], true);

                        return [
                            'tax_id' => $candidate,
                            'country' => 'ES',
                            'kind' => $isNie ? 'nie' : 'nif',
                            'valid' => true,
                            'doc_type' => $isNie ? 'nie' : 'dni',
                            'party_type' => 'particular',
                        ];
                    }
                }
            }
        }

        // 4. Spanish CIF scan
        if (preg_match('/\b([ABCDEFGHJNPQRSUVW]\d{7}[0-9A-J])\b/i', $upper, $matches)) {
            $taxId = strtoupper($matches[1]);

            return [
                'tax_id' => $taxId,
                'country' => 'ES',
                'kind' => 'cif',
                'valid' => $this->spanishValidator->isValid($taxId),
                'doc_type' => 'cif',
                'party_type' => 'sociedad',
            ];
        }

        // 5. Latam Tax IDs (CUIT, RUT, RFC)
        if (preg_match('/\b(20|23|24|27|30|33|34)[\-]?\d{8}[\-]?\d\b/', $upper, $matches)) {
            $taxId = strtoupper($matches[0]);

            return [
                'tax_id' => $taxId,
                'country' => 'AR',
                'kind' => 'cuit',
                'valid' => $this->latamValidator->isValid('AR', $taxId),
                'doc_type' => 'cuit',
                'party_type' => 'particular',
            ];
        }

        if (preg_match('/\b\d{7,8}[\-][0-9K]\b/i', $upper, $matches)) {
            $taxId = strtoupper($matches[0]);

            return [
                'tax_id' => $taxId,
                'country' => 'CL',
                'kind' => 'rut',
                'valid' => $this->latamValidator->isValid('CL', $taxId),
                'doc_type' => 'rut',
                'party_type' => 'particular',
            ];
        }

        if (preg_match('/\b[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}\b/i', $upper, $matches)) {
            $taxId = strtoupper($matches[0]);

            return [
                'tax_id' => $taxId,
                'country' => 'MX',
                'kind' => 'rfc',
                'valid' => $this->latamValidator->isValid('MX', $taxId),
                'doc_type' => 'rfc',
                'party_type' => 'particular',
            ];
        }

        // 6. Fallback unvalidated NIF pattern
        if (preg_match('/(?:[XYZ])[\s\.\-]??\d{7}[\s\.\-]??[A-Z]|\d{1,2}[\s\.\-]??\d{3}[\s\.\-]??\d{3}[\s\.\-]??[A-Z]/i', $upper, $matches)) {
            $clean = strtoupper(preg_replace('/[\s\.\-_]/', '', $matches[0]));
            if (strlen($clean) <= 9) {
                $clean = str_pad($clean, 9, '0', STR_PAD_LEFT);
                $isNie = in_array($clean[0], ['X', 'Y', 'Z'], true);

                return [
                    'tax_id' => $clean,
                    'country' => 'ES',
                    'kind' => $isNie ? 'nie' : 'nif',
                    'valid' => $this->spanishValidator->isValid($clean),
                    'doc_type' => $isNie ? 'nie' : 'dni',
                    'party_type' => 'particular',
                ];
            }
        }

        return [
            'tax_id' => null,
            'country' => 'ES',
            'kind' => 'desconocido',
            'valid' => false,
            'doc_type' => 'desconocido',
            'party_type' => 'particular',
        ];
    }

    /**
     * Extracts full name and components from line-by-line or inline OCR text.
     */
    private function extractNames(array $lines, string $rawText, ?array $mrzResult): array
    {
        if ($mrzResult && ! empty($mrzResult['full_name'])) {
            return [
                'first_name' => $mrzResult['first_name'],
                'last_name' => $mrzResult['last_name'],
                'full_name' => $mrzResult['full_name'],
            ];
        }

        $firstSurname = null;
        $secondSurname = null;
        $firstName = null;

        $isDniLabel = fn (string $s) => (bool) preg_match('/^(?:1[\.ºerª]*\s*APELLIDO|2[\.ºerª]*\s*APELLIDO|APELLIDOS?|NOMBRES?|GIVEN|SURNAME|SEXO|NACIONALIDAD|FECHA|NUM|DNI|NIF|NIE|CIF|VALIDEZ|CADUCIDAD|ESPAÑA|DOCUMENTO|REGISTRO|MINISTERIO|INTERIOR|POLICIA|REINO)/iu', trim($s));

        // Clean noise lines that are purely government headers
        $cleanLines = [];
        foreach ($lines as $line) {
            $t = trim($line);
            if (empty($t)) {
                continue;
            }
            if (preg_match('/^(?:ESPAÑA|REINO DE ESPAÑA|DOCUMENTO NACIONAL DE IDENTIDAD|TARJETA DE IDENTIDAD DE EXTRANJERO|MINISTERIO DEL INTERIOR|DIRECCI[ÓO]N GENERAL DE LA POLIC[IÍ]A|UNION EUROPEA|CÓDIGO|CAN|CLI)$/iu', $t)) {
                continue;
            }
            $cleanLines[] = $t;
        }

        // Multi-line scan
        foreach ($cleanLines as $i => $line) {
            // 1st Surname: "1. APELLIDO", "1er APELLIDO", "1º APELLIDO", "1. SURNAME", "PRIMER APELLIDO"
            if (preg_match('/(?:1[\.ºerª]*\s*APELLIDO|PRIMER\s*APELLIDO|1st\s*SURNAME|1\.\s*SURNAME)/iu', $line)) {
                $val = trim(preg_replace('/.*?(?:1[\.ºerª]*\s*APELLIDO|PRIMER\s*APELLIDO|1st\s*SURNAME|1\.\s*SURNAME)[\s:\.\/]*(?:1st\s*SURNAME)?[\s:\.\/]*/iu', '', $line));
                if (! empty($val) && ! $isDniLabel($val)) {
                    $firstSurname = $val;
                } elseif (isset($cleanLines[$i + 1]) && ! $isDniLabel($cleanLines[$i + 1])) {
                    $firstSurname = $cleanLines[$i + 1];
                }
            }

            // 2nd Surname: "2. APELLIDO", "2º APELLIDO", "2do APELLIDO", "2nd SURNAME", "2. SURNAME", "SEGUNDO APELLIDO"
            if (preg_match('/(?:2[\.ºerª]*\s*APELLIDO|SEGUNDO\s*APELLIDO|2nd\s*SURNAME|2\.\s*SURNAME)/iu', $line)) {
                $val = trim(preg_replace('/.*?(?:2[\.ºerª]*\s*APELLIDO|SEGUNDO\s*APELLIDO|2nd\s*SURNAME|2\.\s*SURNAME)[\s:\.\/]*(?:2nd\s*SURNAME)?[\s:\.\/]*/iu', '', $line));
                if (! empty($val) && ! $isDniLabel($val)) {
                    $secondSurname = $val;
                } elseif (isset($cleanLines[$i + 1]) && ! $isDniLabel($cleanLines[$i + 1])) {
                    $secondSurname = $cleanLines[$i + 1];
                }
            }

            // Given Name: "NOMBRE", "NOMBRE / GIVEN NAME", "NOMBRES", "NOMBRE / NAME"
            if (preg_match('/(?:NOMBRE|GIVEN\s*NAME|NOMBRES)/iu', $line) && ! preg_match('/(?:APELLIDO|PADRE|MADRE|COMPLETO|COMERCIAL|TITULAR|FECHA)/iu', $line)) {
                $val = trim(preg_replace('/.*?(?:NOMBRE|GIVEN\s*NAME|NOMBRES)[\s:\.\/]*(?:GIVEN\s*NAME|NAME)?[\s:\.\/]*/iu', '', $line));
                if (! empty($val) && ! $isDniLabel($val)) {
                    $firstName = $val;
                } elseif (isset($cleanLines[$i + 1]) && ! $isDniLabel($cleanLines[$i + 1])) {
                    $firstName = $cleanLines[$i + 1];
                }
            }

            // Generic "APELLIDOS / SURNAMES" (Passports or single box)
            if ($firstSurname === null && preg_match('/(?:^APELLIDOS|^SURNAMES)/iu', $line)) {
                $val = trim(preg_replace('/.*?(?:APELLIDOS|SURNAMES)[\s:\.\/]*(?:SURNAMES)?[\s:\.\/]*/iu', '', $line));
                if (! empty($val) && ! $isDniLabel($val)) {
                    $firstSurname = $val;
                } elseif (isset($cleanLines[$i + 1]) && ! $isDniLabel($cleanLines[$i + 1])) {
                    $firstSurname = $cleanLines[$i + 1];
                }
            }
        }

        // Inline regex fallback
        if ($firstSurname === null) {
            if (preg_match('/(?:APELLIDOS?|1er\s*APELLIDO|1\s*APELLIDO|1\.\s*APELLIDO|SURNAMES?)[\s:\.]*([A-ZÁÉÍÓÚÑa-záéíóúñ\s\-]+?)(?=\s*(?:2[ºoª]?\s*APELLIDO|NOMBRE|NAME|NACIONALIDAD|SEXO|FECHA|DNI|NIF|CIF|DOMICILIO|\n|$))/iu', $rawText, $m)) {
                $firstSurname = trim(preg_replace('/\s+/', ' ', $m[1]));
            }
        }

        if ($secondSurname === null) {
            if (preg_match('/(?:2[ºoª]?\s*APELLIDO|2\.\s*APELLIDO)[\s:\.]*([A-ZÁÉÍÓÚÑa-záéíóúñ\s\-]+?)(?=\s*(?:NOMBRE|NAME|NACIONALIDAD|SEXO|FECHA|DNI|NIF|CIF|DOMICILIO|\n|$))/iu', $rawText, $m)) {
                $secondSurname = trim(preg_replace('/\s+/', ' ', $m[1]));
            }
        }

        if ($firstName === null) {
            if (preg_match('/(?:NOMBRE|GIVEN\s*NAMES?)[\s:\.]*([A-ZÁÉÍÓÚÑa-záéíóúñ\s]+?)(?=\s*(?:APELLIDOS?|SEXO|NACIONALIDAD|FECHA|DOMICILIO|DNI|NIF|CIF|NUMERO|\n|$))/iu', $rawText, $m)) {
                $firstName = trim(preg_replace('/\s+/', ' ', $m[1]));
            }
        }

        $surnames = trim(implode(' ', array_filter([$firstSurname, $secondSurname])));
        $full = trim(implode(' ', array_filter([$firstName, $surnames])));

        if (empty($full) && preg_match('/(?:TITULAR|NOMBRE\s*COMPLETO|RAZ[ÓO]N\s*SOCIAL)[\s:\.]*([A-ZÁÉÍÓÚÑa-záéíóúñ\s]+?)(?=\s+(?:NIF|CIF|DNI|DOMICILIO|\n|$))/iu', $rawText, $m)) {
            $full = trim($m[1]);
        }

        return [
            'first_name' => $firstName ? ucwords(mb_strtolower($firstName)) : null,
            'last_name' => $surnames ? ucwords(mb_strtolower($surnames)) : null,
            'full_name' => ! empty($full) ? ucwords(mb_strtolower($full)) : null,
        ];
    }

    /**
     * Parses Machine Readable Zone (MRZ) formatted text lines.
     */
    private function parseMrz(string $text): ?array
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", '', $text)))));

        foreach ($lines as $i => $rawLine) {
            $line1 = preg_replace('/[^A-Z0-9<]/', '', strtoupper($rawLine));

            // TD1: 3 lines (Spanish DNI 3.0 / DNIe / TIE)
            if (str_starts_with($line1, 'I') && strlen($line1) >= 28 && isset($lines[$i + 1], $lines[$i + 2])) {
                $line2 = preg_replace('/[^A-Z0-9<]/', '', strtoupper($lines[$i + 1]));
                $line3 = preg_replace('/[^A-Z0-9<]/', '', strtoupper($lines[$i + 2]));

                if (strlen($line2) >= 28 && strlen($line3) >= 28) {
                    $countryCode = substr($line1, 2, 3);
                    $country = $countryCode === 'ESP' ? 'ES' : substr($countryCode, 0, 2);
                    $rawDoc = substr($line1, 5, 9);
                    $taxId = str_replace('<', '', $rawDoc);

                    // Parse names in line 3: SURNAME1<SURNAME2<<FIRSTNAME
                    $names = explode('<<', $line3);
                    $surnames = isset($names[0]) ? str_replace('<', ' ', trim($names[0])) : '';
                    $firstName = isset($names[1]) ? str_replace('<', ' ', trim($names[1])) : '';

                    $taxIdValid = $country === 'ES' ? $this->spanishValidator->isValid($taxId) : true;

                    $expYear = substr($line2, 8, 2);
                    $expMonth = substr($line2, 10, 2);
                    $expDay = substr($line2, 12, 2);
                    $fullYear = (int) $expYear < 50 ? "20{$expYear}" : "19{$expYear}";
                    $expiry = "{$fullYear}-{$expMonth}-{$expDay}";

                    return [
                        'success' => true,
                        'side' => 'back',
                        'document_type' => 'dni',
                        'tax_id' => $taxId,
                        'tax_id_country' => $country,
                        'tax_id_valid' => $taxIdValid,
                        'tax_id_kind' => in_array($taxId[0] ?? '', ['X', 'Y', 'Z'], true) ? 'nie' : 'nif',
                        'party_type' => 'particular',
                        'full_name' => ucwords(mb_strtolower(trim("{$firstName} {$surnames}"))),
                        'first_name' => ucwords(mb_strtolower(trim($firstName))),
                        'last_name' => ucwords(mb_strtolower(trim($surnames))),
                        'address' => null,
                        'postal_code' => null,
                        'city' => null,
                        'country' => $country,
                        'expiry_date' => $expiry,
                    ];
                }
            }
        }

        return null;
    }
}
