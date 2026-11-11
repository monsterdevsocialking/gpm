<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\ProposalDecision;
use App\Models\ProposalVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerProposalController extends Controller
{
    public function show(int $proposalVersion): View
    {
        $version = ProposalVersion::query()
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

        $version = ProposalVersion::query()
            ->whereHas('proposal.quoteRequest', fn ($query) => $query->where('customer_id', Auth::guard('customer')->id()))
            ->with('proposal')
            ->findOrFail($proposalVersion);

        if ($version->status !== 'sent') {
            return back()->withErrors(['decision' => 'Só pode responder a uma versão de proposta que tenha sido emitida.']);
        }

        if ($version->decisions()->where('customer_id', Auth::guard('customer')->id())->exists()) {
            return back()->withErrors(['decision' => 'Já existe uma decisão registada para esta versão. Peça à GPM uma nova versão se precisar de alterações.']);
        }

        ProposalDecision::query()->create([
            'proposal_version_id' => $version->id,
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

        return redirect()->route('customer.proposals.show', $version)
            ->with('success', 'A sua decisão foi registada. A equipa GPM dará seguimento ao processo.');
    }
}
