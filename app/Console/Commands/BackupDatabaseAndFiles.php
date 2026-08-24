<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class BackupDatabaseAndFiles extends Command
{
    protected $signature = 'app:backup {--database-only : Only dump the database} {--files-only : Only archive private files}';

    protected $description = 'Creates a backup of the database and the private storage (PDFs, signatures, evidence).';

    public function handle(): int
    {
        $databaseOnly = (bool) $this->option('database-only');
        $filesOnly = (bool) $this->option('files-only');

        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0775, true);
        }

        $stamp = now()->format('Ymd_His');
        $db = config('database.connections.mysql');

        if (! $filesOnly) {
            $this->dumpDatabase($db, $backupDir, $stamp);
        }

        if (! $databaseOnly) {
            $this->archiveFiles($backupDir, $stamp);
        }

        $this->info("Backup completed in {$backupDir}.");

        return self::SUCCESS;
    }

    private function dumpDatabase(array $db, string $backupDir, string $stamp): void
    {
        $path = "{$backupDir}/db_{$stamp}.sql.gz";

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s %s | gzip > %s',
            escapeshellarg($db['host']),
            escapeshellarg((string) $db['port']),
            escapeshellarg($db['username']),
            escapeshellarg($db['database']),
            escapeshellarg($path)
        );

        $process = Process::fromShellCommandline(
            $command,
            null,
            ['MYSQL_PWD' => (string) ($db['password'] ?? '')]
        );
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('mysqldump failed: '.$process->getErrorOutput());

            return;
        }

        $this->info('Database dumped: '.$path.' ('.round(filesize($path) / 1024, 1).' KB)');
    }

    private function archiveFiles(string $backupDir, string $stamp): void
    {
        $privateRoot = storage_path('app/private');
        $path = "{$backupDir}/files_{$stamp}.tar.gz";

        if (! is_dir($privateRoot)) {
            $this->warn('Private storage not found; skipping files.');

            return;
        }

        $process = new Process(['tar', 'czf', $path, '-C', storage_path('app'), 'private']);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('tar failed: '.$process->getErrorOutput());

            return;
        }

        $this->info('Files archived: '.$path.' ('.round(filesize($path) / 1024, 1).' KB)');
    }
}
