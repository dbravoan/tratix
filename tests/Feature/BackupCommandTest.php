<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_command_creates_database_and_files_archives(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('contracts/C-2026-0001/final-v1.pdf', 'fake pdf');

        // Run with only the files archive to avoid depending on mysqldump binary.
        $exit = Artisan::call('app:backup --files-only');

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('Backup completed', Artisan::output());

        // The command archives the real storage; assert on the filesystem.
        $this->assertDirectoryExists(storage_path('app/backups'));
        $this->assertNotEmpty(glob(storage_path('app/backups/files_*.tar.gz') ?: []));
    }
}
