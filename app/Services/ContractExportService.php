<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ContractExportService
{
    /**
     * Builds a ZIP with all of a user's contract PDFs + evidence files.
     *
     * @return string|null path of the created ZIP, or null if nothing to export.
     */
    public function export(User $user): ?string
    {
        $contracts = $user->contracts()
            ->where(function ($q) {
                $q->whereNotNull('final_pdf_path')
                    ->orWhereNotNull('access_token');
            })
            ->get();

        if ($contracts->isEmpty()) {
            return null;
        }

        $stamp = now()->format('Ymd_His');
        $zipPath = storage_path("app/export_{$user->id}_{$stamp}.zip");

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        foreach ($contracts as $contract) {
            $base = "contracts/{$contract->reference}";

            if ($contract->final_pdf_path && Storage::disk('local')->exists($contract->final_pdf_path)) {
                $zip->addFile(
                    storage_path('app/private/'.$contract->final_pdf_path),
                    "{$contract->reference}/firmado.pdf"
                );
            }

            foreach (['evidence-payload.txt', 'evidence-tsr.txt'] as $file) {
                $path = "{$base}/{$file}";
                if (Storage::disk('local')->exists($path)) {
                    $zip->addFile(storage_path('app/private/'.$path), "{$contract->reference}/{$file}");
                }
            }

            if ($contract->final_hash) {
                $zip->addFromString("{$contract->reference}/hash.txt", $contract->final_hash."\n");
            }
        }

        $zip->close();

        return $zipPath;
    }
}
