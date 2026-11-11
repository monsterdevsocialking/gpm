<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', '');

        $customers = Customer::query()
            ->withCount('orders')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('nif', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($type, ['individual', 'company'], true), fn ($query) => $query->where('customer_type', $type))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.customers.index', compact('customers', 'search', 'type'));
    }

    public function toggleActive(Request $request, int $customer): RedirectResponse
    {
        $record = Customer::query()->findOrFail($customer);
        $newState = ! $record->active;

        $record->forceFill(['active' => $newState])->save();

        ActivityLog::query()->create([
            'user_id' => Auth::guard('web')->id(),
            'action' => $newState ? 'customer_activated' : 'customer_deactivated',
            'entity_type' => 'customer',
            'entity_id' => $record->id,
            'description' => 'Estado da conta do cliente alterado para '.($newState ? 'ativo' : 'inativo').'.',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Estado do cliente atualizado.');
    }
}
