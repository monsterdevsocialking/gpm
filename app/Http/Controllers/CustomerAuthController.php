<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }

        return view('auth.customer-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string'],
        ]);

        $key = 'customer-login:'.strtolower($validated['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'email' => 'Foram feitas demasiadas tentativas. Aguarde um minuto e tente novamente.',
            ])->onlyInput('email');
        }

        $authenticated = Auth::guard('customer')->attempt([
            'email' => strtolower($validated['email']),
            'password' => $validated['password'],
            'active' => true,
        ]);

        if (! $authenticated) {
            RateLimiter::hit($key, 60);

            return back()->withErrors([
                'email' => 'Email ou palavra-passe incorretos, ou conta inativa.',
            ])->onlyInput('email');
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        $customer->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('customer.dashboard'));
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }

        return view('auth.customer-register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);

        $validated = $request->validate([
            'customer_type' => ['required', 'in:individual,company'],
            'name' => ['required', 'string', 'max:180'],
            'nif' => ['nullable', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:190', 'unique:customers,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
        ]);

        $customer = Customer::query()->create([
            ...$validated,
            'email' => strtolower($validated['email']),
        ]);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard')
            ->with('success', 'A sua conta foi criada com sucesso.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
