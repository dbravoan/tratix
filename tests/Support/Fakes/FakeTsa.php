<?php

namespace Tests\Support\Fakes;

use App\Services\TsaService;

class FakeTsa extends TsaService
{
    public function __construct()
    {
        parent::__construct('https://fake.tsa.test/tsr', 1);
    }

    public function timestamp(string $filePath): ?array
    {
        return [
            'tsr_base64' => base64_encode('FAKE-TSR-TOKEN'),
            'token_time' => 'Aug 18 2026 00:00:00 UTC',
        ];
    }

    public function url(): string
    {
        return 'https://fake.tsa.test/tsr';
    }
}
