<?php

namespace App\Console\Commands;

use App\Mail\MonthlySummaryMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMonthlySummaries extends Command
{
    protected $signature = 'contracts:monthly-summary';

    protected $description = 'Sends a monthly summary email to each active user.';

    public function handle(): int
    {
        $count = 0;

        User::where('email_verified_at', '!=', null)
            ->where('notify_summary', true)
            ->where('created_at', '<=', now()->subDays(3))
            ->get()
            ->each(function (User $user) use (&$count) {
                $total = $user->contracts()->count();
                $signed = $user->contracts()->where('status', 'firmado')->count();
                $pending = $user->contracts()->whereIn('status', ['en_revision', 'en_firma'])->count();

                Mail::to($user->email)->queue(new MonthlySummaryMail($user, $total, $signed, $pending));
                $count++;
            });

        $this->info("Resúmenes enviados: {$count}.");

        return self::SUCCESS;
    }
}
