<?php

namespace App\Http\Controllers;

use App\Enums\QuoteRequestStatus;
use App\Models\QuoteRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class AdminQuoteRequestController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $requests = QuoteRequest::query()
            ->withCount('items')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('request_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest('submitted_at')
            ->paginate(20)
            ->withQueryString();

        $statuses = QuoteRequestStatus::cases();

        return view('admin.requests.index', compact('requests', 'search', 'status', 'statuses'));
    }

    public function show(int $quoteRequest): View
    {
        $quoteRequest = QuoteRequest::query()
            ->with(['customer', 'items.product', 'statusHistory.user', 'supplierInquiries.supplier', 'supplierInquiries.responses.items', 'proposal.latestVersion.decisions.customer'])
            ->findOrFail($quoteRequest);

        $statuses = QuoteRequestStatus::cases();
        $suppliers = Supplier::query()->where('active', true)->orderBy('name')->get();

        return view('admin.requests.show', compact('quoteRequest', 'statuses', 'suppliers'));
    }

    public function updateStatus(Request $request, int $quoteRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(QuoteRequestStatus::values())],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);

        $transitions = [
            'submitted' => ['under_review', 'cancelled'],
            'under_review' => ['supplier_research', 'proposal_preparation', 'cancelled'],
            'supplier_research' => ['under_review', 'proposal_preparation', 'cancelled'],
            'proposal_preparation' => ['supplier_research', 'proposal_sent', 'cancelled'],
            'proposal_sent' => ['proposal_preparation', 'cancelled'],
            'closed' => [],
            'cancelled' => [],
        ];

        DB::transaction(function () use ($quoteRequest, $validated, $transitions): void {
            $record = QuoteRequest::query()->lockForUpdate()->findOrFail($quoteRequest);
            $oldStatus = $record->status;
            $newStatus = $validated['status'];

            if ($oldStatus === $newStatus) {
                return;
            }

            if (! in_array($newStatus, $transitions[$oldStatus] ?? [], true)) {
                throw ValidationException::withMessages(['status' => 'Esta alteração de estado não é permitida. Verifique a etapa atual do pedido.']);
            }

            $record->update(['status' => $newStatus]);

            $record->statusHistory()->create([
                'user_id' => Auth::guard('web')->id(),
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'note' => trim((string) ($validated['note'] ?? '')) ?: null,
            ]);
        });

        return redirect()->route('admin.requests.show', $quoteRequest)
            ->with('success', 'Estado do pedido atualizado e registado no histórico.');
    }
}
