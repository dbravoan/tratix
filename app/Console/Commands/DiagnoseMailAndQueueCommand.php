<?php

namespace App\Console\Commands;

use App\Jobs\DiagnoseQueueJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DiagnoseMailAndQueueCommand extends Command
{
    protected $signature = 'mail:diagnose 
                            {email? : Dirección de correo electrónico de prueba}
                            {--process-now : Procesar inmediatamente la cola durante la prueba}';

    protected $description = 'Diagnostica la configuración de correo SMTP, el estado de la base de datos de colas y comprueba si el worker está activo.';

    public function handle(): int
    {
        $this->info('===========================================================');
        $this->info('  🔍 DIAGNÓSTICO DEL SISTEMA DE CORREO Y COLAS DE TRATIX   ');
        $this->info('===========================================================');

        $targetEmail = $this->argument('email') ?: config('mail.from.address');

        // 1. CONFIGURACIÓN
        $this->info("\n📋 1. Configuración del Entorno:");
        $queueConn = config('queue.default');
        $mailer = config('mail.default');
        $host = config('mail.mailers.smtp.host');
        $port = config('mail.mailers.smtp.port');
        $encryption = config('mail.mailers.smtp.encryption') ?: 'ninguna';
        $username = config('mail.mailers.smtp.username');
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');
        $otpEnabled = config('signing.otp_enabled') ? 'SÍ (Activo)' : 'NO (Desactivado)';

        $this->table(
            ['Variable / Parámetro', 'Valor Actual'],
            [
                ['Conexión de Colas (QUEUE_CONNECTION)', $queueConn],
                ['Driver de Correo (MAIL_MAILER)', $mailer],
                ['Servidor SMTP (MAIL_HOST)', $host ?: '(no definido)'],
                ['Puerto SMTP (MAIL_PORT)', $port ?: '(no definido)'],
                ['Cifrado (MAIL_ENCRYPTION)', $encryption],
                ['Usuario SMTP (MAIL_USERNAME)', $username ?: '(vacío)'],
                ['Remitente (MAIL_FROM_ADDRESS)', "{$fromName} <{$fromAddress}>"],
                ['Verificación OTP de Firma (SIGNING_OTP_ENABLED)', $otpEnabled],
            ]
        );

        // 2. INSPECCIÓN DE BASE DE DATOS Y COLAS
        $this->info("\n🗄️ 2. Estado de la Base de Datos y Colas:");
        $dbConnected = false;
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
            $this->line('  <info>✓ Conexión a la base de datos:</info> Correcta');
        } catch (Throwable $e) {
            $this->warn("  ⚠️ Error conectando a la base de datos: {$e->getMessage()}");
        }

        if ($dbConnected) {
            $hasJobsTable = Schema::hasTable('jobs');
            $hasFailedTable = Schema::hasTable('failed_jobs');

            $this->line('  ' . ($hasJobsTable ? '<info>✓ Tabla `jobs`:</info> Presente' : '<error>✗ Tabla `jobs` no existe (ejecuta php artisan migrate)</error>'));
            $this->line('  ' . ($hasFailedTable ? '<info>✓ Tabla `failed_jobs`:</info> Presente' : '<error>✗ Tabla `failed_jobs` no existe</error>'));

            if ($hasJobsTable) {
                $pendingJobs = DB::table('jobs')->count();
                if ($pendingJobs > 0) {
                    $this->warn("  ⚠️ Trabajos pendientes en cola actualmente: {$pendingJobs}");
                } else {
                    $this->line("  <info>✓ Trabajos pendientes en cola:</info> 0 (al día)");
                }
            }

            if ($hasFailedTable) {
                $failedCount = DB::table('failed_jobs')->count();
                if ($failedCount > 0) {
                    $this->error("  ✗ Trabajos fallidos registrados en `failed_jobs`: {$failedCount}");
                    $recentFailed = DB::table('failed_jobs')->latest('failed_at')->limit(2)->get();
                    foreach ($recentFailed as $failed) {
                        $this->line("    - Fallo ID {$failed->id} ({$failed->failed_at}): " . substr(strip_tags($failed->exception), 0, 140) . '...');
                    }
                    $this->line("    👉 Para ver el detalle o reintentar: php artisan queue:failed / php artisan queue:retry all\n");
                } else {
                    $this->line("  <info>✓ Trabajos fallidos:</info> 0");
                }
            }
        }

        // 3. CONECTIVIDAD DE RED SMTP
        $this->info("\n🌐 3. Prueba de Conectividad de Red con Servidor SMTP:");
        if ($mailer === 'smtp' && $host && $port) {
            $this->line("  Probando conexión socket TCP a {$host}:{$port}...");
            $socketTimeout = 5;
            $fp = @fsockopen($host, (int) $port, $errno, $errstr, $socketTimeout);
            if ($fp) {
                $greeting = fgets($fp, 512);
                fclose($fp);
                $this->line("  <info>✓ Conexión TCP exitosa a {$host}:{$port}</info>");
                if ($greeting) {
                    $this->line("    Respuesta del servidor: <comment>" . trim($greeting) . "</comment>");
                }
            } else {
                $this->error("  ✗ No se puede conectar a {$host}:{$port} (Error {$errno}: {$errstr})");
                $this->warn("  👉 Posibles causas:");
                $this->warn("     - El puerto {$port} está bloqueado por el firewall del servidor o proveedor (ej. OVH/Hetzner).");
                $this->warn("     - El host '{$host}' no resuelve por DNS o no es correcto.");
            }
        } else {
            $this->line("  (Driver no es SMTP o faltan datos de host/puerto)");
        }

        // 4. ENVÍO DIRECTO SÍNCRONO DE PRUEBA
        if ($targetEmail) {
            $this->info("\n✉️ 4. Prueba de Envío Directo (Síncrono sin cola):");
            $this->line("  Enviando email de prueba a: <comment>{$targetEmail}</comment>...");
            try {
                Mail::raw("Este es un correo de prueba de diagnóstico directo enviado desde Tratix.\nFecha y hora: " . now()->toDateTimeString(), function ($message) use ($targetEmail) {
                    $message->to($targetEmail)
                        ->subject('🧪 Prueba de Diagnóstico Directo - Tratix');
                });
                $this->line("  <info>✓ ¡Correo directo enviado con ÉXITO a {$targetEmail}!</info>");
            } catch (Throwable $e) {
                $this->error("  ✗ Error en el envío directo SMTP:");
                $this->error("    Clase: " . get_class($e));
                $this->error("    Mensaje: " . $e->getMessage());
                $this->warn("  👉 Verifica las credenciales MAIL_USERNAME, MAIL_PASSWORD y MAIL_ENCRYPTION en tu .env.");
            }
        }

        // 5. PRUEBA DE COLA Y COMPROBACIÓN DE WORKER
        $this->info("\n⚡ 5. Prueba de Despacho a Cola y Estado del Worker:");
        $testId = uniqid('diag_');
        $this->line("  Despachando trabajo de prueba (ID: <comment>{$testId}</comment>) a la cola '{$queueConn}'...");

        try {
            DiagnoseQueueJob::dispatch($testId, $targetEmail);

            if ($queueConn === 'sync') {
                $this->info("  ✓ QUEUE_CONNECTION=sync (El trabajo se ejecutó de forma síncrona de inmediato).");
            } else {
                $this->line("  Trabajo insertado en la cola. Comprobando si un Worker activo lo procesa...");
                $this->output->write("  Esperando al worker");

                $consumed = false;
                for ($i = 0; $i < 6; $i++) {
                    sleep(1);
                    $this->output->write('.');
                    $result = Cache::get("queue_test_result:{$testId}");
                    if ($result) {
                        $consumed = true;
                        break;
                    }
                }
                $this->line('');

                if ($consumed) {
                    $this->info("  🎉 ¡ÉXITO! El Worker de colas está ACTIVO y procesó el trabajo correctamente.");
                } else {
                    $this->error("  ❌ ATENCIÓN: El trabajo sigue en la cola y NINGÚN worker lo ha procesado tras 6 segundos.");
                    $this->warn("\n  👉 DIAGNÓSTICO: El Queue Worker no se está ejecutando en tu servidor.");
                    $this->line("     Para solucionarlo:");
                    $this->line("     1. Si usas Plesk Laravel Toolkit: Activa el 'Queue Worker' en la pestaña Laravel de Plesk.");
                    $this->line("     2. Para procesar manualmente los trabajos ahora mismo desde la terminal:");
                    $this->line("        <comment>php artisan queue:work database --once</comment>");
                    $this->line("     3. Si usas Systemd: <comment>sudo systemctl start tratix-worker</comment>");
                    
                    if ($this->option('process-now')) {
                        $this->info("\n  ⚙️ Ejecutando un pase del worker con --process-now...");
                        $this->call('queue:work', ['--once' => true]);
                    }
                }
            }
        } catch (Throwable $e) {
            $this->error("  ✗ Error al encolar el trabajo de prueba: " . $e->getMessage());
            $this->warn("  👉 Asegúrate de que las tablas de base de datos (`jobs`) existen y están accesibles.");
        }

        $this->info("\n===========================================================");
        $this->info('  Diagnóstico finalizado.                                 ');
        $this->info('===========================================================');

        return self::SUCCESS;
    }
}
