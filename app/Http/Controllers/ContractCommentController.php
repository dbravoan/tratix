<?php

namespace App\Http\Controllers;

use App\Mail\ContractCommentMail;
use App\Models\AuditEvent;
use App\Models\Contract;
use App\Models\ContractComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContractCommentController extends Controller
{
    /**
     * Store comment by contract creator / authenticated user.
     */
    public function store(Request $request, Contract $contract): RedirectResponse
    {
        $this->authorize('update', $contract);

        $data = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
            'clause_key' => ['nullable', 'string', 'max:100'],
            'clause_title' => ['nullable', 'string', 'max:255'],
        ]);

        $authorRole = $contract->creator_role ?? 'creador';
        $authorName = auth()->user()->name ?? 'Creador';

        $comment = ContractComment::create([
            'contract_id' => $contract->id,
            'user_id' => auth()->id(),
            'author_name' => $authorName,
            'author_role' => $authorRole,
            'clause_key' => $data['clause_key'] ?? null,
            'clause_title' => $data['clause_title'] ?? null,
            'content' => trim($data['content']),
        ]);

        AuditEvent::create([
            'contract_id' => $contract->id,
            'user_id' => auth()->id(),
            'event' => 'comment_added',
            'actor' => $authorName,
            'detail' => 'Comentario añadido '.($comment->clause_title ? "en cláusula «{$comment->clause_title}»" : 'al contrato').'.',
            'happened_at' => now(),
        ]);

        // Notify counterparty via email if known
        $counterparty = $contract->counterparty();
        $targetEmail = $counterparty?->email ?? $contract->invited_email;
        if ($targetEmail && $contract->access_token) {
            try {
                Mail::to($targetEmail)->queue(
                    new ContractCommentMail($contract, $comment, route('review.show', $contract->access_token))
                );
            } catch (\Throwable $e) {
                Log::warning('comment.counterparty_notify_failed', ['error' => $e->getMessage()]);
            }
        }

        return back()->with('success', 'Comentario publicado correctamente.');
    }

    /**
     * Store comment by counterparty via review token.
     */
    public function storePublic(Request $request, string $token): RedirectResponse
    {
        $contract = Contract::where('access_token', $token)->firstOrFail();

        if (! $contract->tokenIsValid()) {
            return back()->with('error', 'El enlace de revisión ha caducado o no es válido.');
        }

        $data = $request->validate([
            'author_name' => ['required', 'string', 'max:255'],
            'author_role' => ['required', 'string', 'in:vendedor,comprador'],
            'content' => ['required', 'string', 'max:2000'],
            'clause_key' => ['nullable', 'string', 'max:100'],
            'clause_title' => ['nullable', 'string', 'max:255'],
        ]);

        $comment = ContractComment::create([
            'contract_id' => $contract->id,
            'user_id' => null,
            'author_name' => trim($data['author_name']),
            'author_role' => $data['author_role'],
            'clause_key' => $data['clause_key'] ?? null,
            'clause_title' => $data['clause_title'] ?? null,
            'content' => trim($data['content']),
        ]);

        AuditEvent::create([
            'contract_id' => $contract->id,
            'user_id' => null,
            'event' => 'comment_added',
            'actor' => $data['author_name'].' ('.ucfirst($data['author_role']).')',
            'detail' => 'Comentario de contraparte '.($comment->clause_title ? "en cláusula «{$comment->clause_title}»" : 'al contrato').'.',
            'happened_at' => now(),
        ]);

        // Notify contract creator if preference is enabled
        $contract->loadMissing('user');
        if ($contract->user?->email && ($contract->user->notify_comments ?? true)) {
            try {
                Mail::to($contract->user->email)->queue(
                    new ContractCommentMail($contract, $comment, route('contracts.show', $contract))
                );
            } catch (\Throwable $e) {
                Log::warning('comment.creator_notify_failed', ['error' => $e->getMessage()]);
            }
        }

        return back()->with('success', 'Tu comentario u observación ha sido registrado para el creador del contrato.');
    }
}
