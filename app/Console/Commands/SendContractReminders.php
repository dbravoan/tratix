<?php

namespace App\Console\Commands;

use App\Mail\ReminderMail;
use App\Models\Contract;
use App\Services\ContractWorkflowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendContractReminders extends Command
{
    protected $signature = 'contracts:reminders {--dry-run : Print what would be sent without sending}';

    protected $description = 'Sends automatic reminders for contracts awaiting review/signature and expiring links.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $sent = 0;

        $this->warn('Avisos de firma pendiente (en_firma)');
        $sent += $this->remindSigning($dryRun);

        $this->warn('Avisos de revisión pendiente (en_revision)');
        $sent += $this->remindReview($dryRun);

        $this->info("Enviados/emitidos: {$sent} recordatorios.");

        return self::SUCCESS;
    }

    private function remindSigning(bool $dryRun): int
    {
        $count = 0;

        Contract::with(['user', 'auditEvents'])
            ->where('status', 'en_firma')
            ->where('created_at', '<=', now()->subDays(2))
            ->where('created_at', '>', now()->subDays(14))
            ->get()
            ->each(function (Contract $contract) use ($dryRun, &$count) {
                $recentlyReminded = $contract->auditEvents
                    ->where('event', 'reminder_signing_sent')
                    ->where('created_at', '>=', now()->subDays(5))
                    ->isNotEmpty();

                if ($recentlyReminded) {
                    return;
                }

                $url = route('contracts.show', $contract);
                $message = 'La contraparte aún no ha firmado el contrato '.$contract->reference.'. Recuerda compartir el enlace de firma.';

                if ($dryRun) {
                    $this->line("  [dry] firma {$contract->reference} -> {$contract->user?->email}");
                } elseif ($contract->user) {
                    Mail::to($contract->user->email)->queue(new ReminderMail($contract, $url, $message));
                    app(ContractWorkflowService::class)->record(
                        $contract,
                        'reminder_signing_sent',
                        'system',
                        detail: 'Recordatorio automático de firma enviado.'
                    );
                }

                $count++;
            });

        return $count;
    }

    private function remindReview(bool $dryRun): int
    {
        $count = 0;

        Contract::with(['user', 'auditEvents'])
            ->where('status', 'en_revision')
            ->where('created_at', '<=', now()->subDays(3))
            ->where('created_at', '>', now()->subDays(14))
            ->get()
            ->each(function (Contract $contract) use ($dryRun, &$count) {
                $recentlyReminded = $contract->auditEvents
                    ->where('event', 'reminder_review_sent')
                    ->where('created_at', '>=', now()->subDays(5))
                    ->isNotEmpty();

                if ($recentlyReminded) {
                    return;
                }

                $url = route('contracts.show', $contract);
                $message = 'El contrato '.$contract->reference.' sigue pendiente de revisión. Recuerda compartir el enlace de revisión.';

                if ($dryRun) {
                    $this->line("  [dry] revision {$contract->reference} -> {$contract->user?->email}");
                } elseif ($contract->user) {
                    Mail::to($contract->user->email)->queue(new ReminderMail($contract, $url, $message));
                    app(ContractWorkflowService::class)->record(
                        $contract,
                        'reminder_review_sent',
                        'system',
                        detail: 'Recordatorio automático de revisión enviado.'
                    );
                }

                $count++;
            });

        return $count;
    }
}
