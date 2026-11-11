<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerAddressController extends Controller
{
    public function index(): View
    {
        $customer = Auth::guard('customer')->user();
        $addresses = $customer->addresses()->orderByDesc('is_default')->orderBy('id')->get();

        return view('customer.addresses.index', compact('addresses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();
        $validated = $this->validatedAddress($request);

        DB::transaction(function () use ($customer, $validated): void {
            $isFirstAddress = ! $customer->addresses()->exists();

            if ($isFirstAddress) {
                $validated['is_default'] = true;
            }

            if (! empty($validated['is_default'])) {
                $customer->addresses()->update(['is_default' => false]);
            }

            $customer->addresses()->create($validated);
        });

        return redirect()->route('customer.addresses.index')
            ->with('success', 'Morada adicionada com sucesso.');
    }

    public function update(Request $request, int $address): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();
        $record = $customer->addresses()->findOrFail($address);
        $validated = $this->validatedAddress($request);

        DB::transaction(function () use ($customer, $record, $validated): void {
            if ($record->is_default) {
                $validated['is_default'] = true;
            }

            if (! empty($validated['is_default'])) {
                $customer->addresses()->where('id', '!=', $record->getKey())->update(['is_default' => false]);
            }

            $record->update($validated);
        });

        return redirect()->route('customer.addresses.index')
            ->with('success', 'Morada actualizada com sucesso.');
    }

    public function makeDefault(int $address): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();
        $record = $customer->addresses()->findOrFail($address);

        DB::transaction(function () use ($customer, $record): void {
            $customer->addresses()->update(['is_default' => false]);
            $record->update(['is_default' => true]);
        });

        return redirect()->route('customer.addresses.index')
            ->with('success', 'Morada principal actualizada.');
    }

    public function destroy(int $address): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();
        $record = $customer->addresses()->findOrFail($address);
        $wasDefault = $record->is_default;

        DB::transaction(function () use ($customer, $record, $wasDefault): void {
            $record->delete();

            if ($wasDefault) {
                $nextAddress = $customer->addresses()->orderBy('id')->first();

                if ($nextAddress) {
                    $nextAddress->update(['is_default' => true]);
                }
            }
        });

        return redirect()->route('customer.addresses.index')
            ->with('success', 'Morada removida com sucesso.');
    }

    private function validatedAddress(Request $request): array
    {
        return $request->validate([
            'label' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'required_without:street', 'string', 'max:2000'],
            'street' => ['nullable', 'required_without:address', 'string', 'max:180'],
            'neighborhood' => ['nullable', 'string', 'max:120'],
            'reference_point' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
        ]);
    }
}
