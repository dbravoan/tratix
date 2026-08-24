<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;

/**
 * RFC 3161 Time-Stamp Authority client using the system OpenSSL.
 *
 * Produces a signed Time-Stamp Token (TSR) for a given file and returns it
 * base64-encoded. The TSA endpoint is configurable (default: FreeTSA). When
 * the authority is unreachable the service returns null and the evidence
 * certificate falls back to the server timestamp — documented as such, so the
 * sealing process never blocks on an external dependency.
 */
class TsaService
{
    public function __construct(
        private readonly string $url = 'https://freetsa.org/tsr',
        private readonly int $timeout = 6,
    ) {}

    public function url(): string
    {
        return $this->url;
    }

    /**
     * @return array{tsr_base64: string, token_time?: string}|null null on any failure
     */
    public function timestamp(string $filePath): ?array
    {
        $queryFile = tempnam(sys_get_temp_dir(), 'tsq');
        $replyFile = tempnam(sys_get_temp_dir(), 'tsr');

        try {
            $query = Process::timeout($this->timeout)->run(
                ['openssl', 'ts', '-query', '-data', $filePath, '-sha256', '-cert', '-out', $queryFile]
            );

            if ($query->exitCode() !== 0) {
                return null;
            }

            $post = Process::timeout($this->timeout)
                ->input(file_get_contents($queryFile))
                ->run([
                    'curl', '--silent', '--show-error', '--max-time', (string) $this->timeout,
                    '--output', $replyFile, '--data-binary', '@-',
                    '--header', 'Content-Type: application/timestamp-query',
                    $this->url,
                ]);

            if ($post->exitCode() !== 0 || ! is_file($replyFile) || filesize($replyFile) === 0) {
                return null;
            }

            $tsr = file_get_contents($replyFile);
            if ($tsr === false) {
                return null;
            }

            return [
                'tsr_base64' => base64_encode($tsr),
                'token_time' => $this->extractTokenTime($replyFile),
            ];
        } catch (\Throwable) {
            return null;
        } finally {
            @unlink($queryFile);
            @unlink($replyFile);
        }
    }

    private function extractTokenTime(string $replyFile): ?string
    {
        $verify = Process::timeout($this->timeout)->run(
            ['openssl', 'ts', '-reply', '-in', $replyFile, '-text']
        );

        if ($verify->exitCode() !== 0) {
            return null;
        }

        if (preg_match('/Time stamp:\s*(.+)/', $verify->output(), $match)) {
            return trim($match[1]);
        }

        return null;
    }
}
