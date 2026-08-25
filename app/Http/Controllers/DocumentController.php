<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractDocument;
use App\Services\DocumentGuidanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    public function __construct(private readonly DocumentGuidanceService $guidance) {}

    public function index(Contract $contract): View
    {
        $this->authorize('view', $contract);

        $contract->load('documents');
        $checklist = $this->guidance->checklist($contract);
        $extraDocuments = $this->guidance->extraDocuments($contract);
        $completeness = $this->guidance->completeness($contract);

        return view('contracts.documents', compact('contract', 'checklist', 'extraDocuments', 'completeness'));
    }

    public function upload(Request $request, Contract $contract): RedirectResponse
    {
        $this->authorize('update', $contract);

        $data = $request->validate([
            'requirement_key' => ['nullable', 'string', 'max:64'],
            'custom_label' => ['nullable', 'string', 'max:128'],
            'document' => ['required', 'file', 'max:10240', 'mimes:pdf,png,jpg,jpeg,webp', 'mimetypes:application/pdf,image/png,image/jpeg,image/webp'],
        ]);

        $requirementKey = ! empty($data['requirement_key'])
            ? $data['requirement_key']
            : (! empty($data['custom_label']) ? \Illuminate\Support\Str::slug($data['custom_label'], '_') : 'documento_adicional');

        $file = $request->file('document');
        $diskName = config('filesystems.documents_disk', 'local');
        $path = $file->store('documents/'.$contract->reference, $diskName);

        ContractDocument::create([
            'contract_id' => $contract->id,
            'requirement_key' => $requirementKey,
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'status' => 'uploaded',
            'uploaded_by_user_id' => auth()->id(),
            'uploaded_at' => now(),
        ]);

        return back()->with('success', 'Documento adjuntado correctamente.');
    }

    public function validateDocument(Contract $contract, ContractDocument $document): RedirectResponse
    {
        $this->authorize('update', $contract);
        abort_unless($document->contract_id === $contract->id, 404);

        $document->update(['status' => 'validated', 'validated_at' => now()]);

        return back()->with('success', 'Documento marcado como revisado.');
    }

    public function download(Contract $contract, ContractDocument $document): Response
    {
        $this->authorize('view', $contract);
        abort_unless($document->contract_id === $contract->id, 404);

        $diskName = config('filesystems.documents_disk', 'local');
        $disk = Storage::disk($diskName);
        if (! $disk->exists($document->path) && Storage::disk('local')->exists($document->path)) {
            $disk = Storage::disk('local');
        }

        abort_unless($disk->exists($document->path), 404, 'El archivo solicitado no se encuentra en el almacenamiento.');

        return $disk->download($document->path, $document->filename);
    }

    public function destroy(Contract $contract, ContractDocument $document): RedirectResponse
    {
        $this->authorize('update', $contract);
        abort_unless($document->contract_id === $contract->id, 404);

        $diskName = config('filesystems.documents_disk', 'local');
        if (Storage::disk($diskName)->exists($document->path)) {
            Storage::disk($diskName)->delete($document->path);
        } elseif (Storage::disk('local')->exists($document->path)) {
            Storage::disk('local')->delete($document->path);
        }

        $document->delete();

        return back()->with('success', 'Documento eliminado.');
    }
}
