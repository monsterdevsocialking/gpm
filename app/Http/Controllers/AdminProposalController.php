<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\ProposalVersion;
use App\Models\QuoteRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class AdminProposalController extends Controller
{
    public function startDraft(int $quoteRequest): RedirectResponse
    {
        $requestRecord = QuoteRequest::query()->with(['items', 'proposal.latestVersion.decisions'])->findOrFail($quoteRequest);

        if ($requestRecord->status !== 'proposal_preparation') {
            return back()->withErrors(['proposal' => 'Coloque o pedido na etapa de preparação da proposta antes de criar uma proposta.']);
        }

        $version = DB::transaction(function () use ($requestRecord): ProposalVersion {
            $proposal = $requestRecord->proposal;

            if (! $proposal) {
                $proposal = Proposal::query()->create([
                    'quote_request_id' => $requestRecord->id,
                    'proposal_number' => 'GPM-PROP-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                    'status' => 'draft',
                    'created_by' => Auth::guard('web')->id(),
                ]);
            }

            $latest = $proposal->versions()->with('decisions')->first();

            if ($latest && $latest->status === 'draft') {
                return $latest;
            }

            if ($latest) {
                $decision = $latest->decisions->sortByDesc('decided_at')->first();

                if (! $decision || ! in_array($decision->decision, ['rejected', 'revision_requested'], true)) {
                    throw ValidationException::withMessages([
                        'proposal' => 'A versão atual ainda aguarda decisão ou já foi aceite. Não é possível abrir outra versão.',
                    ]);
                }
            }

            $nextNumber = (int) $proposal->versions()->max('version_number') + 1;

            $version = $proposal->versions()->create([
                'version_number' => $nextNumber,
                'status' => 'draft',
                'created_by' => Auth::guard('web')->id(),
            ]);

            foreach ($requestRecord->items as $requestItem) {
                $version->items()->create([
                    'quote_request_item_id' => $requestItem->id,
                    'product_id' => $requestItem->product_id,
                    'product_name' => $requestItem->product_name,
                    'product_sku' => $requestItem->product_sku,
                    'unit' => $requestItem->unit,
                    'quantity' => $requestItem->quantity,
                    'unit_price' => null,
                    'line_total' => null,
                ]);
            }

            $proposal->update(['status' => 'draft']);

            return $version;
        });

        return redirect()->route('admin.proposals.show', $version)
            ->with('success', 'Rascunho da proposta criado. Preencha os valores e condições antes de emitir.');
    }

    public function show(int $proposalVersion): View
    {
        $version = ProposalVersion::query()
            ->with(['proposal.quoteRequest.customer', 'items', 'decisions.customer'])
            ->findOrFail($proposalVersion);

        return view('admin.proposals.show', compact('version'));
    }

    public function updateDraft(Request $request, int $proposalVersion): RedirectResponse
    {
        $version = ProposalVersion::query()->with('items')->findOrFail($proposalVersion);

        if (! $version->isEditable()) {
            abort(403, 'Esta versão já foi emitida e não pode ser alterada.');
        }

        $itemIds = $version->items->modelKeys();

        $validated = $request->validate([
            'currency' => ['nullable', 'string', 'size:3', 'alpha'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:today'],
            'terms' => ['nullable', 'string', 'max:10000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'distinct', Rule::in($itemIds)],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'items.*.notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($version, $validated): void {
            $version = ProposalVersion::query()->lockForUpdate()->findOrFail($version->id);

            if (! $version->isEditable()) {
                abort(403, 'Esta versão já foi emitida e não pode ser alterada.');
            }

            $subtotal = 0.0;
            $fullyPriced = true;

            foreach ($validated['items'] as $line) {
                $item = $version->items()->whereKey($line['id'])->firstOrFail();
                $unitPrice = $line['unit_price'] ?? null;
                $lineTotal = $unitPrice === null ? null : round((float) $item->quantity * (float) $unitPrice, 2);

                $item->update([
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'notes' => trim((string) ($line['notes'] ?? '')) ?: null,
                ]);

                if ($lineTotal === null) {
                    $fullyPriced = false;
                } else {
                    $subtotal += $lineTotal;
                }
            }

            $version->update([
                'currency' => isset($validated['currency']) ? strtoupper($validated['currency']) : null,
                'valid_until' => $validated['valid_until'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'subtotal' => $fullyPriced ? round($subtotal, 2) : null,
            ]);
        });

        return redirect()->route('admin.proposals.show', $proposalVersion)
            ->with('success', 'Rascunho da proposta guardado.');
    }

    public function issue(int $proposalVersion): RedirectResponse
    {
        DB::transaction(function () use ($proposalVersion): void {
            $version = ProposalVersion::query()
                ->with(['items', 'proposal.quoteRequest'])
                ->lockForUpdate()
                ->findOrFail($proposalVersion);

            if (! $version->isEditable()) {
                throw ValidationException::withMessages(['proposal' => 'Esta versão já foi emitida.']);
            }

            if (! $version->valid_until || $version->valid_until->lt(today())) {
                throw ValidationException::withMessages(['valid_until' => 'Defina uma validade igual ou posterior à data de hoje.']);
            }

            if (! $version->currency || ! preg_match('/^[A-Z]{3}$/', $version->currency)) {
                throw ValidationException::withMessages(['currency' => 'Defina uma moeda ISO de três letras antes de emitir.']);
            }

            if ($version->items->isEmpty() || $version->items->contains(fn ($item) => $item->unit_price === null)) {
                throw ValidationException::withMessages(['items' => 'Defina o preço unitário de todos os artigos antes de emitir.']);
            }

            $subtotal = 0.0;

            foreach ($version->items as $item) {
                $lineTotal = round((float) $item->quantity * (float) $item->unit_price, 2);
                $item->update(['line_total' => $lineTotal]);
                $subtotal += $lineTotal;
            }

            $version->update([
                'subtotal' => round($subtotal, 2),
                'status' => 'sent',
                'issued_at' => now(),
            ]);

            $proposal = $version->proposal;
            $proposal->update(['status' => 'sent']);

            $quoteRequest = $proposal->quoteRequest;
            if ($quoteRequest->status !== 'proposal_preparation') {
                throw ValidationException::withMessages(['proposal' => 'O pedido já não está na etapa de preparação da proposta.']);
            }

            $quoteRequest->update(['status' => 'proposal_sent']);
            $quoteRequest->statusHistory()->create([
                'user_id' => Auth::guard('web')->id(),
                'old_status' => 'proposal_preparation',
                'new_status' => 'proposal_sent',
                'note' => 'Proposta '.$proposal->proposal_number.' versão '.$version->version_number.' emitida.',
            ]);
        });

        return redirect()->route('admin.proposals.show', $proposalVersion)
            ->with('success', 'Proposta emitida. O cliente poderá analisar esta versão na sua área.');
    }
}
