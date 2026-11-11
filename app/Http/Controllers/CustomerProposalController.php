<?php

namespace App\Http\Controllers;

use App\Models\ProposalVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerProposalController extends Controller
{
    public function show(int $proposalVersion): View
    {
        $version = ProposalVersion::query()
            ->where('status', 'sent')
            ->whereHas('proposal.quoteRequest', fn ($query) => $query->where('customer_id', Auth::guard('customer')->id()))
            ->with(['proposal.quoteRequest', 'items', 'decisions'])
            ->findOrFail($proposalVersion);

        return view('customer.proposals.show', compact('version'));
    }

    public function decide(Request $request, int $proposalVersion): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:accepted,rejected,revision_requested'],
            'comment' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($proposalVersion, $validated): void {
            $version = ProposalVersion::query()
                ->whereHas('proposal.quoteRequest', fn ($query) => $query->where('customer_id', Auth::guard('customer')->id()))
                ->with('proposal.quoteRequest')
                ->lockForUpdate()
                ->findOrFail($proposalVersion);

            if ($version->status !== 'sent' || ! $version->issued_at) {
                throw ValidationException::withMessages([
                    'decision' => 'Só pode responder a uma versão de proposta que tenha sido emitida.',
                ]);
            }

            $quoteRequest = $version->proposal->quoteRequest;

            if ($quoteRequest->status !== 'proposal_sent') {
                throw ValidationException::withMessages([
                    'decision' => 'Este pedido já não está à espera de uma decisão sobre a proposta.',
                ]);
            }

            if (! $version->valid_until || $version->valid_until->lt(today())) {
                throw ValidationException::withMessages([
                    'decision' => 'A validade desta proposta terminou. Contacte a GPM para receber uma nova versão.',
                ]);
            }

            if ($version->decisions()->where('customer_id', Auth::guard('customer')->id())->exists()) {
                throw ValidationException::withMessages([
                    'decision' => 'Já existe uma decisão registada para esta versão. Peça à GPM uma nova versão se precisar de alterações.',
                ]);
            }

            $version->decisions()->create([
                'customer_id' => Auth::guard('customer')->id(),
                'decision' => $validated['decision'],
                'comment' => trim((string) ($validated['comment'] ?? '')) ?: null,
                'decided_at' => now(),
            ]);

            $proposalStatus = match ($validated['decision']) {
                'accepted' => 'accepted',
                'rejected' => 'rejected',
                'revision_requested' => 'revision_requested',
            };

            $version->proposal->update(['status' => $proposalStatus]);

            if (in_array($validated['decision'], ['rejected', 'revision_requested'], true)) {
                $quoteRequest->update(['status' => 'proposal_preparation']);
                $quoteRequest->statusHistory()->create([
                    'user_id' => null,
                    'old_status' => 'proposal_sent',
                    'new_status' => 'proposal_preparation',
                    'note' => $validated['decision'] === 'revision_requested'
                        ? 'O cliente solicitou alterações à proposta. Preparar uma nova versão.'
                        : 'O cliente rejeitou a proposta. A equipa pode preparar uma nova versão se houver continuidade comercial.',
                ]);
            }
        });

        return redirect()->route('customer.proposals.show', $proposalVersion)
            ->with('success', 'A sua decisão foi registada. A equipa GPM dará seguimento ao processo.');
    }
}
