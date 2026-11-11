<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function editProfile(): View
    {
        $customer = Auth::guard('customer')->user();

        return view('customer.account.profile', compact('customer'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'email' => [
                'required',
                'email',
                'max:190',
                Rule::unique('customers', 'email')->ignore($customer->getKey()),
            ],
            'phone' => ['required', 'string', 'max:40'],
            'nif' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:2000'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['email'] = strtolower(trim($validated['email']));

        $customer->fill($validated)->save();
        Auth::guard('customer')->setUser($customer->fresh());

        $request->session()->regenerate();

        return redirect()->route('customer.profile.edit')
            ->with('success', 'Os seus dados foram atualizados.');
    }

    public function editPassword(): View
    {
        return view('customer.account.password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $customer = Auth::guard('customer')->user();

        if (! Hash::check($validated['current_password'], $customer->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'A palavra-passe atual está incorreta.',
            ]);
        }

        $customer->forceFill([
            'password' => $validated['password'],
        ])->save();

        $request->session()->regenerate();

        return redirect()->route('customer.password.edit')
            ->with('success', 'A sua palavra-passe foi alterada com sucesso.');
    }
}
