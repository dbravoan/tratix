<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DiagnoseMailAndQueueCommandTest extends TestCase
{
    public function test_diagnose_command_runs_successfully(): void
    {
        Mail::fake();

        $this->artisan('mail:diagnose', ['email' => 'test@example.com'])
            ->expectsOutputToContain('DIAGNÓSTICO DEL SISTEMA DE CORREO Y COLAS')
            ->assertExitCode(0);
    }
}
