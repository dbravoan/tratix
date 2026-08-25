<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Robust service to parse Spanish, Latin American & International identity documents
 * (DNI 3.0/4.0 Anverso & Reverso, NIE/TIE, CIF, Passports, LATAM IDs).
 * Extracts structured data (Full Name, 1st Surname, 2nd Surname, Given Name, Tax ID, Support Number, Complete Address, CP, City, Province, Country, Expiry).
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
     *   support_number: ?string,
     *   address: ?string,
     *   postal_code: ?string,
     *   city: ?string,
     *   province: ?string,
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
     * Extracts text content from a file (PDF text streams, or returns empty string for binary photos).
     */
    public function extractRawText(UploadedFile $file): string
    {
        $mime = $file->getMimeType() ?: '';
        $path = $file->getRealPath();

        // 1. Text extraction for PDFs
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

        // 2. Binary Image Files (JPEG, PNG, WEBP, etc.):
        // NEVER treat binary image bytes as UTF-8 text, as it generates garbled noise (e.g. Yhne0gpctf... and xy).
        // If server-side tesseract binary is installed, we can run it. Otherwise, return empty string.
        if (function_exists('exec')) {
            $tesseractBin = trim((string) @exec('which tesseract 2>/dev/null'));
            if (! empty($tesseractBin) && file_exists($path)) {
                $outputBase = tempnam(sys_get_temp_dir(), 'tess_ocr');
                @exec(escapeshellcmd("{$tesseractBin} ".escapeshellarg($path)." ".escapeshellarg($outputBase)." -l spa+eng --oem 1 --psm 3 2>/dev/null"));
                $txtFile = "{$outputBase}.txt";
                if (file_exists($txtFile)) {
                    $text = (string) file_get_contents($txtFile);
                    @unlink($txtFile);
                    @unlink($outputBase);

                    return $text;
                }
                @unlink($outputBase);
            }
        }

        return '';
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

        // 2. Extract Tax ID (NIF / NIE / CIF / LATAM / EU) using precision algorithms
        $taxInfo = $this->extractTaxId($cleanText, $mrzResult);
        $taxId = $taxInfo['tax_id'];
        $taxIdCountry = $taxInfo['country'];
        $taxIdKind = $taxInfo['kind'];
        $taxIdValid = $taxInfo['valid'];
        $docType = $taxInfo['doc_type'];
        $partyType = $taxInfo['party_type'];
        $supportNumber = $mrzResult['support_number'] ?? $this->extractSupportNumber($cleanText);

        // 3. Extract Names (First Surname, Second Surname, Given Name)
        $nameInfo = $this->extractNames($lines, $cleanText, $mrzResult);
        $firstName = $nameInfo['first_name'];
        $lastName = $nameInfo['last_name'];
        $fullName = $nameInfo['full_name'];

        // 4. Extract Complete Address, City, Postal Code, Province
        $addressInfo = $this->extractAddress($cleanText, $taxIdCountry);
        $address = $addressInfo['address'];
        $postalCode = $addressInfo['postal_code'];
        $city = $addressInfo['city'];
        $province = $addressInfo['province'];

        // 5. Expiration date
        $expiryDate = $mrzResult['expiry_date'] ?? null;
        if ($expiryDate === null && preg_match('/(?:VALIDEZ|CADUCIDAD|VENCE|EXPIR(?:Y|ES)?|VENCIMIENTO)[\s:\.]*(\d{2}[\/\-\s\.]\d{2}[\/\-\s\.]\d{4})/iu', $cleanText, $m)) {
            $expiryDate = str_replace(['/', '.', ' '], '-', $m[1]);
        }

        // 6. Detect side (Front vs Back)
        $detectedSide = $requestedSide !== 'auto' ? $requestedSide : 'unknown';
        if ($detectedSide === 'unknown') {
            $upper = strtoupper($cleanText);
            if ($mrzResult !== null || $address !== null || $city !== null || str_contains($upper, 'DOMICILIO') || str_contains($upper, 'HIJO DE') || str_contains($upper, 'EQUIPO')) {
                $detectedSide = 'back';
            } elseif ($firstName !== null || $lastName !== null || str_contains($upper, 'APELLIDO') || str_contains($upper, 'NACIONALIDAD') || str_contains($upper, 'FECHA DE NACIMIENTO')) {
                $detectedSide = 'front';
            }
        }

        $success = $taxId !== null || $fullName !== null || $address !== null;

        return [
            'success' => $success,
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
            'support_number' => $supportNumber,
            'address' => $address,
            'postal_code' => $postalCode,
            'city' => $city,
            'province' => $province,
            'country' => $taxIdCountry,
            'expiry_date' => $expiryDate,
        ];
    }

    /**
     * Extracts and validates Tax ID (NIF / NIE / CIF / LATAM / EU) from text or MRZ.
     */
    private function extractTaxId(string $text, ?array $mrzResult): array
    {
        // If MRZ yielded a validated tax ID, use it directly
        if ($mrzResult && ! empty($mrzResult['tax_id']) && ! empty($mrzResult['tax_id_valid'])) {
            return [
                'tax_id' => $mrzResult['tax_id'],
                'country' => $mrzResult['tax_id_country'],
                'kind' => $mrzResult['tax_id_kind'],
                'valid' => true,
                'doc_type' => $mrzResult['document_type'],
                'party_type' => $mrzResult['party_type'],
            ];
        }

        $upper = strtoupper($text);

        // 1. Spanish DNI / NIF (8 digits + control letter) with mathematical checksum validation
        if (preg_match_all('/(?:DNI|NIF|NUM|N[ºo]|DOCUMENTO|ID|IDESP)?[\s:\.\/ºª#\-]*([0-9][\d\s\.\-]{6,14}[A-Z])/i', $upper, $matches)) {
            foreach ($matches[1] as $raw) {
                $clean = strtoupper(preg_replace('/[\s\.\-_]/', '', $raw));
                if (strlen($clean) === 9 && $this->spanishValidator->isValid($clean)) {
                    return [
                        'tax_id' => $clean,
                        'country' => 'ES',
                        'kind' => 'nif',
                        'valid' => true,
                        'doc_type' => 'dni',
                        'party_type' => 'particular',
                    ];
                }
            }
        }

        // 2. Spanish NIE (X/Y/Z + 7 digits + control letter) with mathematical checksum validation
        if (preg_match_all('/(?:NIE|TIE|EXTRANJERO)?[\s:\.\/ºª#\-]*([XYZ][\d\s\.\-]{6,14}[A-Z])/i', $upper, $matches)) {
            foreach ($matches[1] as $raw) {
                $clean = strtoupper(preg_replace('/[\s\.\-_]/', '', $raw));
                if (strlen($clean) === 9 && $this->spanishValidator->isValid($clean)) {
                    return [
                        'tax_id' => $clean,
                        'country' => 'ES',
                        'kind' => 'nie',
                        'valid' => true,
                        'doc_type' => 'nie',
                        'party_type' => 'particular',
                    ];
                }
            }
        }

        // 3. Spanish CIF (Corporate Tax ID)
        if (preg_match_all('/(?:CIF|NIF)?[\s:\.\/ºª#\-]*([ABCDEFGHJNPQRSUVW][\d\s\.\-]{6,14}[0-9A-J])/i', $upper, $matches)) {
            foreach ($matches[1] as $raw) {
                $clean = strtoupper(preg_replace('/[\s\.\-_]/', '', $raw));
                if (strlen($clean) === 9 && $this->spanishValidator->isValid($clean)) {
                    return [
                        'tax_id' => $clean,
                        'country' => 'ES',
                        'kind' => 'cif',
                        'valid' => true,
                        'doc_type' => 'cif',
                        'party_type' => 'sociedad',
                    ];
                }
            }
        }

        // 4. OCR Fixes for Spanish DNI/NIE (e.g. O->0, I/l->1, B->8 in number parts)
        if (preg_match_all('/(?:[XYZ\dOIlB][\s\.\-_]*){8,12}[A-Z]/i', $upper, $matches)) {
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

        // 5. Argentina: CUIT / CUIL (20|23|24|27|30|33|34-XXXXXXXX-X) & DNI
        if (preg_match('/(?:CUIT|CUIL)?[\s:\.\/ºª#\-]*\b(20|23|24|27|30|33|34)[\s\-._]?(\d{8})[\s\-._]?(\d)\b/', $upper, $matches)) {
            $taxId = "{$matches[1]}-{$matches[2]}-{$matches[3]}";
            if ($this->latamValidator->isValid('AR', $taxId)) {
                return [
                    'tax_id' => $taxId,
                    'country' => 'AR',
                    'kind' => 'cuit',
                    'valid' => true,
                    'doc_type' => 'cuit',
                    'party_type' => in_array($matches[1], ['30', '33', '34'], true) ? 'sociedad' : 'particular',
                ];
            }
        }

        // 6. Chile: RUN / RUT (12.345.678-K)
        if (preg_match('/\b\d{7,8}[\-][0-9K]\b/i', $upper, $matches)) {
            $taxId = strtoupper($matches[0]);
            if ($this->latamValidator->isValid('CL', $taxId)) {
                return [
                    'tax_id' => $taxId,
                    'country' => 'CL',
                    'kind' => 'rut',
                    'valid' => true,
                    'doc_type' => 'rut',
                    'party_type' => 'particular',
                ];
            }
        }

        // 7. Mexico: RFC (Personas Físicas / Morales) & CURP
        if (preg_match('/\b[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}\b/i', $upper, $matches)) {
            $taxId = strtoupper($matches[0]);
            if ($this->latamValidator->isValid('MX', $taxId)) {
                $isMoral = strlen($taxId) === 12;

                return [
                    'tax_id' => $taxId,
                    'country' => 'MX',
                    'kind' => 'rfc',
                    'valid' => true,
                    'doc_type' => 'rfc',
                    'party_type' => $isMoral ? 'sociedad' : 'particular',
                ];
            }
        }

        // 8. Colombia: NIT / Cédula
        if (preg_match('/\b\d{9}[\-]\d\b/', $upper, $matches)) {
            $taxId = strtoupper($matches[0]);
            if ($this->latamValidator->isValid('CO', $taxId)) {
                return [
                    'tax_id' => $taxId,
                    'country' => 'CO',
                    'kind' => 'nit',
                    'valid' => true,
                    'doc_type' => 'nit',
                    'party_type' => 'sociedad',
                ];
            }
        }

        // 9. Peru: RUC (10/20) & DNI (8 digits)
        if (preg_match('/\b(10|15|17|20)\d{9}\b/', $upper, $matches)) {
            $taxId = strtoupper($matches[0]);
            if ($this->latamValidator->isValid('PE', $taxId)) {
                return [
                    'tax_id' => $taxId,
                    'country' => 'PE',
                    'kind' => 'ruc',
                    'valid' => true,
                    'doc_type' => 'ruc',
                    'party_type' => str_starts_with($taxId, '20') ? 'sociedad' : 'particular',
                ];
            }
        }

        // 10. Uruguay: RUT (12 digits) & Cédula
        if (preg_match('/\b\d{12}\b/', $upper, $matches)) {
            $taxId = strtoupper($matches[0]);
            if ($this->latamValidator->isValid('UY', $taxId)) {
                return [
                    'tax_id' => $taxId,
                    'country' => 'UY',
                    'kind' => 'rut',
                    'valid' => true,
                    'doc_type' => 'rut',
                    'party_type' => 'sociedad',
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
     * Extracts full name and components with strict human name validation.
     */
    private function extractNames(array $lines, string $rawText, ?array $mrzResult): array
    {
        if ($mrzResult && ! empty($mrzResult['full_name']) && $this->isValidHumanName($mrzResult['full_name'])) {
            return [
                'first_name' => $mrzResult['first_name'],
                'last_name' => $mrzResult['last_name'],
                'full_name' => $mrzResult['full_name'],
            ];
        }

        $firstSurname = null;
        $secondSurname = null;
        $firstName = null;

        $isDniLabel = fn (string $s) => (bool) preg_match('/^(?:1[\.ºerª]*\s*APELLIDO|2[\.ºerª]*\s*APELLIDO|APELLIDOS?|NOMBRES?|GIVEN|SURNAME|SEXO|NACIONALIDAD|FECHA|NUM|DNI|NIF|NIE|CIF|VALIDEZ|CADUCIDAD|ESPAÑA|DOCUMENTO|REGISTRO|MINISTERIO|INTERIOR|POLICIA|REINO|EQUIPO|CAN|CLI|DOMICILIO|MUNICIPIO|PROVINCIA|PAIS)/iu', trim($s));

        // Clean noise lines that are purely government headers
        $cleanLines = [];
        foreach ($lines as $line) {
            $t = trim($line);
            if (empty($t)) {
                continue;
            }
            if (preg_match('/^(?:ESPAÑA|REINO DE ESPAÑA|DOCUMENTO NACIONAL DE IDENTIDAD|TARJETA DE IDENTIDAD DE EXTRANJERO|MINISTERIO DEL INTERIOR|DIRECCI[ÓO]N GENERAL DE LA POLIC[IÍ]A|UNION EUROPEA|CÓDIGO|CAN|CLI|REPÚBLICA|INSTITUTO NACIONAL ELECTORAL|CREDENCIAL PARA VOTAR)$/iu', $t)) {
                continue;
            }
            $cleanLines[] = $t;
        }

        // Multi-line scan
        foreach ($cleanLines as $i => $line) {
            // 1st Surname: "1. APELLIDO", "1er APELLIDO", "1º APELLIDO", "1. SURNAME", "PRIMER APELLIDO"
            if (preg_match('/(?:1[\.ºerª]*\s*APELLIDO|PRIMER\s*APELLIDO|1st\s*SURNAME|1\.\s*SURNAME)/iu', $line)) {
                $val = trim(preg_replace('/.*?(?:1[\.ºerª]*\s*APELLIDO|PRIMER\s*APELLIDO|1st\s*SURNAME|1\.\s*SURNAME)[\s:\.\/]*(?:1st\s*SURNAME)?[\s:\.\/]*/iu', '', $line));
                if (! empty($val) && ! $isDniLabel($val) && $this->isValidHumanName($val)) {
                    $firstSurname = $val;
                } elseif (isset($cleanLines[$i + 1]) && ! $isDniLabel($cleanLines[$i + 1]) && $this->isValidHumanName($cleanLines[$i + 1])) {
                    $firstSurname = $cleanLines[$i + 1];
                }
            }

            // 2nd Surname: "2. APELLIDO", "2º APELLIDO", "2do APELLIDO", "2nd SURNAME", "2. SURNAME", "SEGUNDO APELLIDO"
            if (preg_match('/(?:2[\.ºerª]*\s*APELLIDO|SEGUNDO\s*APELLIDO|2nd\s*SURNAME|2\.\s*SURNAME)/iu', $line)) {
                $val = trim(preg_replace('/.*?(?:2[\.ºerª]*\s*APELLIDO|SEGUNDO\s*APELLIDO|2nd\s*SURNAME|2\.\s*SURNAME)[\s:\.\/]*(?:2nd\s*SURNAME)?[\s:\.\/]*/iu', '', $line));
                if (! empty($val) && ! $isDniLabel($val) && $this->isValidHumanName($val)) {
                    $secondSurname = $val;
                } elseif (isset($cleanLines[$i + 1]) && ! $isDniLabel($cleanLines[$i + 1]) && $this->isValidHumanName($cleanLines[$i + 1])) {
                    $secondSurname = $cleanLines[$i + 1];
                }
            }

            // Given Name: "NOMBRE", "NOMBRE / GIVEN NAME", "NOMBRES", "NOMBRE / NAME"
            if (preg_match('/(?:NOMBRE|GIVEN\s*NAME|NOMBRES)/iu', $line) && ! preg_match('/(?:APELLIDO|PADRE|MADRE|COMPLETO|COMERCIAL|TITULAR|FECHA)/iu', $line)) {
                $val = trim(preg_replace('/.*?(?:NOMBRE|GIVEN\s*NAME|NOMBRES)[\s:\.\/]*(?:GIVEN\s*NAME|NAME)?[\s:\.\/]*/iu', '', $line));
                if (! empty($val) && ! $isDniLabel($val) && $this->isValidHumanName($val)) {
                    $firstName = $val;
                } elseif (isset($cleanLines[$i + 1]) && ! $isDniLabel($cleanLines[$i + 1]) && $this->isValidHumanName($cleanLines[$i + 1])) {
                    $firstName = $cleanLines[$i + 1];
                }
            }

            // Generic "APELLIDOS / SURNAMES" (Passports, INE or single box)
            if ($firstSurname === null && preg_match('/(?:^APELLIDOS|^SURNAMES)/iu', $line)) {
                $val = trim(preg_replace('/.*?(?:APELLIDOS|SURNAMES)[\s:\.\/]*(?:SURNAMES)?[\s:\.\/]*/iu', '', $line));
                if (! empty($val) && ! $isDniLabel($val) && $this->isValidHumanName($val)) {
                    $firstSurname = $val;
                } elseif (isset($cleanLines[$i + 1]) && ! $isDniLabel($cleanLines[$i + 1]) && $this->isValidHumanName($cleanLines[$i + 1])) {
                    $firstSurname = $cleanLines[$i + 1];
                }
            }
        }

        // Inline regex fallback
        if ($firstSurname === null) {
            if (preg_match('/(?:APELLIDOS?|1er\s*APELLIDO|1\s*APELLIDO|1\.\s*APELLIDO|SURNAMES?)[\s:\.]*([A-ZÁÉÍÓÚÑa-záéíóúñ\s\-]+?)(?=\s*(?:2[ºoª]?\s*APELLIDO|NOMBRE|NAME|NACIONALIDAD|SEXO|FECHA|DNI|NIF|CIF|DOMICILIO|\n|$))/iu', $rawText, $m)) {
                $candidate = trim(preg_replace('/\s+/', ' ', $m[1]));
                if ($this->isValidHumanName($candidate)) {
                    $firstSurname = $candidate;
                }
            }
        }

        if ($secondSurname === null) {
            if (preg_match('/(?:2[ºoª]?\s*APELLIDO|2\.\s*APELLIDO)[\s:\.]*([A-ZÁÉÍÓÚÑa-záéíóúñ\s\-]+?)(?=\s*(?:NOMBRE|NAME|NACIONALIDAD|SEXO|FECHA|DNI|NIF|CIF|DOMICILIO|\n|$))/iu', $rawText, $m)) {
                $candidate = trim(preg_replace('/\s+/', ' ', $m[1]));
                if ($this->isValidHumanName($candidate)) {
                    $secondSurname = $candidate;
                }
            }
        }

        if ($firstName === null) {
            if (preg_match('/(?:NOMBRE|GIVEN\s*NAMES?)[\s:\.]*([A-ZÁÉÍÓÚÑa-záéíóúñ\s]+?)(?=\s*(?:APELLIDOS?|SEXO|NACIONALIDAD|FECHA|DOMICILIO|DNI|NIF|CIF|NUMERO|\n|$))/iu', $rawText, $m)) {
                $candidate = trim(preg_replace('/\s+/', ' ', $m[1]));
                if ($this->isValidHumanName($candidate)) {
                    $firstName = $candidate;
                }
            }
        }

        $surnames = trim(implode(' ', array_filter([$firstSurname, $secondSurname])));
        $full = trim(implode(' ', array_filter([$firstName, $surnames])));

        if (empty($full) && preg_match('/(?:TITULAR|NOMBRE\s*COMPLETO|RAZ[ÓO]N\s*SOCIAL)[\s:\.]*([A-ZÁÉÍÓÚÑa-záéíóúñ\s]+?)(?=\s+(?:NIF|CIF|DNI|DOMICILIO|\n|$))/iu', $rawText, $m)) {
            $candidate = trim($m[1]);
            if ($this->isValidHumanName($candidate)) {
                $full = $candidate;
            }
        }

        $validFull = $this->isValidHumanName($full) ? ucwords(mb_strtolower($full)) : null;

        return [
            'first_name' => $firstName && $this->isValidHumanName($firstName) ? ucwords(mb_strtolower($firstName)) : null,
            'last_name' => $surnames && $this->isValidHumanName($surnames) ? ucwords(mb_strtolower($surnames)) : null,
            'full_name' => $validFull,
        ];
    }

    /**
     * Extracts complete address, city, postal code and province from document text.
     */
    private function extractAddress(string $text, string $country = 'ES'): array
    {
        $address = null;
        $postalCode = null;
        $city = null;
        $province = null;

        // 1. Street / Domicilio
        if (preg_match('/(?:DOMICILIO|DIRECCI[ÓO]N|CALLE|AVDA|AVENIDA|PASEO|PLAZA|C\/)[\s:\.]*([A-Z0-9ÁÉÍÓÚÑa-záéíóúñ\s,\.\/ºª\-#]+?)(?=\s*(?:MUNICIPIO|LOCALIDAD|CIUDAD|POBLACI[ÓO]N|CP|C\.P\.|PROVINCIA|ESTADO|PA[IÍ]S|IDESP|EQUIPO|\n|$))/iu', $text, $m)) {
            $candidate = trim(preg_replace('/\s+/', ' ', $m[1]));
            if (strlen($candidate) >= 3 && strlen($candidate) <= 120) {
                $address = $candidate;
            }
        }

        // 2. City / Municipality
        if (preg_match('/(?:MUNICIPIO|LOCALIDAD|CIUDAD|POBLACI[ÓO]N|ALCALD[IÍ]A|COMUNA|DISTRITO)[\s:\.]*([A-ZÁÉÍÓÚÑa-záéíóúñ\s]+?)(?=\s*(?:PROVINCIA|ESTADO|DEPARTAMENTO|CP|C\.P\.|PA[IÍ]S|VALIDEZ|CADUCIDAD|IDESP|EQUIPO|\n|$))/iu', $text, $m)) {
            $candidate = trim(preg_replace('/\s+/', ' ', $m[1]));
            if (strlen($candidate) >= 2 && strlen($candidate) <= 50) {
                $city = ucwords(mb_strtolower($candidate));
            }
        }

        // 3. Province / State
        if (preg_match('/(?:PROVINCIA|ESTADO|DEPARTAMENTO|REGI[ÓO]N)[\s:\.]*([A-ZÁÉÍÓÚÑa-záéíóúñ\s]+?)(?=\s*(?:PA[IÍ]S|CP|C\.P\.|VALIDEZ|CADUCIDAD|IDESP|EQUIPO|\n|$))/iu', $text, $m)) {
            $candidate = trim(preg_replace('/\s+/', ' ', $m[1]));
            if (strlen($candidate) >= 2 && strlen($candidate) <= 50) {
                $province = ucwords(mb_strtolower($candidate));
            }
        }

        // 4. Postal Code
        // Spain postal code: 01000 - 52999
        if (preg_match('/\b(0[1-9]|[1-4]\d|5[0-2])\d{3}\b/', $text, $m)) {
            $postalCode = $m[0];
        } elseif (preg_match('/(?:CP|C\.P\.|C[ÓO]DIGO\s*POSTAL)[\s:\.]*(\d{4,6})\b/i', $text, $m)) {
            $postalCode = $m[1];
        }

        return [
            'address' => $address,
            'postal_code' => $postalCode,
            'city' => $city,
            'province' => $province,
        ];
    }

    /**
     * Parses Machine Readable Zone (MRZ) formatted text lines.
     * Supports:
     *   - ICAO TD1 (3 lines of 30 chars): Spanish DNI 3.0/4.0, DNIe, TIE, EU ID Cards.
     *   - ICAO TD3 (2 lines of 44 chars): Passports.
     */
    private function parseMrz(string $text): ?array
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", '', $text)))));

        foreach ($lines as $i => $rawLine) {
            $line1 = preg_replace('/[^A-Z0-9<]/', '', strtoupper($rawLine));

            // TD1: 3 lines (Spanish DNI 3.0 / DNIe / TIE / EU ID cards)
            if (str_starts_with($line1, 'I') && strlen($line1) >= 28 && isset($lines[$i + 1], $lines[$i + 2])) {
                $line2 = preg_replace('/[^A-Z0-9<]/', '', strtoupper($lines[$i + 1]));
                $line3 = preg_replace('/[^A-Z0-9<]/', '', strtoupper($lines[$i + 2]));

                if (strlen($line2) >= 28 && strlen($line3) >= 28) {
                    $countryCode = substr($line1, 2, 3);
                    $country = $countryCode === 'ESP' ? 'ES' : substr($countryCode, 0, 2);

                    // Line 1: Document Control / Support Number (e.g. CKL159690)
                    $rawSupport = substr($line1, 5, 9);
                    $supportNumber = str_replace('<', '', $rawSupport);

                    // Line 2: Personal Tax ID (DNI / NIF real in Spain is located in optional data of line 2 or embedded)
                    $realTaxId = null;
                    $taxIdValid = false;

                    // Scan line 2 for standard Spanish NIF / NIE (e.g. 12345678Z or X1234567L)
                    if (preg_match('/([XYZ]\d{7}[A-Z]|\d{8}[A-Z])/i', $line2, $m)) {
                        $candidate = strtoupper($m[1]);
                        if ($this->spanishValidator->isValid($candidate)) {
                            $realTaxId = $candidate;
                            $taxIdValid = true;
                        }
                    }

                    // If not found in line 2, check if document number itself is valid NIF/NIE
                    if (! $realTaxId && $this->spanishValidator->isValid($supportNumber)) {
                        $realTaxId = $supportNumber;
                        $taxIdValid = true;
                    }

                    // Parse names in line 3: SURNAME1<SURNAME2<<FIRSTNAME
                    $names = explode('<<', $line3);
                    $surnames = isset($names[0]) ? str_replace('<', ' ', trim($names[0])) : '';
                    $firstName = isset($names[1]) ? str_replace('<', ' ', trim($names[1])) : '';

                    $expYear = substr($line2, 8, 2);
                    $expMonth = substr($line2, 10, 2);
                    $expDay = substr($line2, 12, 2);
                    $fullYear = (int) $expYear < 50 ? "20{$expYear}" : "19{$expYear}";
                    $expiry = "{$fullYear}-{$expMonth}-{$expDay}";

                    $fullName = trim("{$firstName} {$surnames}");

                    return [
                        'success' => true,
                        'side' => 'back',
                        'document_type' => 'dni',
                        'tax_id' => $realTaxId,
                        'tax_id_country' => $country,
                        'tax_id_valid' => $taxIdValid,
                        'tax_id_kind' => ($realTaxId && in_array($realTaxId[0], ['X', 'Y', 'Z'], true)) ? 'nie' : 'nif',
                        'party_type' => 'particular',
                        'full_name' => $this->isValidHumanName($fullName) ? ucwords(mb_strtolower($fullName)) : null,
                        'first_name' => $this->isValidHumanName($firstName) ? ucwords(mb_strtolower($firstName)) : null,
                        'last_name' => $this->isValidHumanName($surnames) ? ucwords(mb_strtolower($surnames)) : null,
                        'support_number' => $supportNumber ?: null,
                        'address' => null,
                        'postal_code' => null,
                        'city' => null,
                        'province' => null,
                        'country' => $country,
                        'expiry_date' => $expiry,
                    ];
                }
            }

            // TD3: 2 lines of 44 chars (Passports)
            if (str_starts_with($line1, 'P') && strlen($line1) >= 40 && isset($lines[$i + 1])) {
                $line2 = preg_replace('/[^A-Z0-9<]/', '', strtoupper($lines[$i + 1]));
                if (strlen($line2) >= 40) {
                    $countryCode = substr($line1, 2, 3);
                    $country = $countryCode === 'ESP' ? 'ES' : substr($countryCode, 0, 2);

                    $namePart = substr($line1, 5);
                    $names = explode('<<', $namePart);
                    $surnames = isset($names[0]) ? str_replace('<', ' ', trim($names[0])) : '';
                    $firstName = isset($names[1]) ? str_replace('<', ' ', trim($names[1])) : '';

                    $rawPassport = substr($line2, 0, 9);
                    $passportNo = str_replace('<', '', $rawPassport);

                    $personalNo = substr($line2, 28, 14);
                    $cleanPersonal = str_replace('<', '', $personalNo);

                    $realTaxId = null;
                    if ($this->spanishValidator->isValid($cleanPersonal)) {
                        $realTaxId = $cleanPersonal;
                    }

                    $fullName = trim("{$firstName} {$surnames}");

                    return [
                        'success' => true,
                        'side' => 'front',
                        'document_type' => 'pasaporte',
                        'tax_id' => $realTaxId ?: $passportNo,
                        'tax_id_country' => $country,
                        'tax_id_valid' => $realTaxId !== null,
                        'tax_id_kind' => 'pasaporte',
                        'party_type' => 'particular',
                        'full_name' => $this->isValidHumanName($fullName) ? ucwords(mb_strtolower($fullName)) : null,
                        'first_name' => $this->isValidHumanName($firstName) ? ucwords(mb_strtolower($firstName)) : null,
                        'last_name' => $this->isValidHumanName($surnames) ? ucwords(mb_strtolower($surnames)) : null,
                        'support_number' => $passportNo,
                        'address' => null,
                        'postal_code' => null,
                        'city' => null,
                        'province' => null,
                        'country' => $country,
                        'expiry_date' => null,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Extracts support/card number (e.g. CKL159690, IDESP...) from general document text.
     */
    private function extractSupportNumber(string $text): ?string
    {
        if (preg_match('/(?:SOPORTE|N[ºo]\s*SOPORTE|DOCUMENTO|IDESP|CAN|CLI)[\s:\.]*([A-Z0-9]{8,12})/i', $text, $m)) {
            return strtoupper(trim($m[1]));
        }

        return null;
    }

    /**
     * Strict validator to ensure string is a plausible human name (not OCR/binary noise).
     */
    public function isValidHumanName(?string $name): bool
    {
        if ($name === null) {
            return false;
        }

        $clean = trim($name);
        if (mb_strlen($clean) < 2 || mb_strlen($clean) > 80) {
            return false;
        }

        // Must only contain letters, accents, spaces, hyphens, and apostrophes (NO digits)
        if (! preg_match('/^[\p{L}\s\-\'\.]+$/u', $clean)) {
            return false;
        }

        // Each individual word must be reasonable length (2 to 25 chars)
        $words = preg_split('/\s+/u', $clean);
        if (empty($words) || count($words) > 8) {
            return false;
        }

        foreach ($words as $w) {
            $len = mb_strlen($w);
            if ($len < 1 || $len > 25) {
                return false;
            }
            // Discard random consonant clusters (binary noise)
            if ($len >= 5 && ! preg_match('/[aeiouáéíóúüäëïöü]/iu', $w)) {
                return false;
            }
        }

        return true;
    }
}
