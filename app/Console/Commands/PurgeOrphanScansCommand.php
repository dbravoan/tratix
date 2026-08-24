<?php

namespace App\Console\Commands;

use App\Models\ContractDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeOrphanScansCommand extends Command
{
    protected $signature = 'contracts:purge-orphan-scans {--hours=24 : Age in hours to consider temporary scan files orphaned}';

    protected $description = 'Securely purges unattached temporary ID card scans older than specified hours (GDPR Data Minimization)';

    public function handle(): int
    {
        $disk = Storage::disk('local');
        $directory = 'documents/temp_scans';

        if (! $disk->exists($directory)) {
            $this->info('No temporary scans directory found.');

            return self::SUCCESS;
        }

        $hours = (int) $this->option('hours');
        $threshold = now()->subHours($hours)->getTimestamp();
        $files = $disk->files($directory);

        $purgedCount = 0;

        foreach ($files as $file) {
            $lastModified = $disk->lastModified($file);

            if ($lastModified <= $threshold) {
                // Check if file path is linked to any ContractDocument
                $isLinked = ContractDocument::where('path', $file)->exists();

                if (! $isLinked) {
                    $disk->delete($file);
                    $purgedCount++;
                }
            }
        }

        $this->info("Purged {$purgedCount} orphaned temporary scan files older than {$hours} hours (GDPR Art. 5.1.e).");

        return self::SUCCESS;
    }
}
