<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DiagnoseQueueJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $testId,
        public ?string $targetEmail = null
    ) {}

    public function handle(): void
    {
        Log::info("DiagnoseQueueJob executed for test ID: {$this->testId}");
        Cache::put("queue_test_result:{$this->testId}", [
            'status' => 'success',
            'executed_at' => now()->toIso8601String(),
            'target_email' => $this->targetEmail,
        ], now()->addMinutes(10));

        if ($this->targetEmail) {
            Mail::raw("Este es un correo de prueba enviado desde la COLA de Tratix (Job ID: {$this->testId}).\nFecha: ".now()->toDateTimeString(), function ($message) {
                $message->to($this->targetEmail)
                    ->subject('✅ Prueba de Cola Tratix: Notificación encolada exitosa');
            });
        }
    }
}
