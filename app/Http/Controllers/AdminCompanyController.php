<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CompanySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminCompanyController extends Controller
{
    public function edit(): View
    {
        $settings = CompanySetting::query()->first();

        if (! $settings) {
            $settings = CompanySetting::query()->create([
                'company_name' => 'GPM',
                'currency' => 'AOA',
                'delivery_fee' => 0,
            ]);
        }

        return view('admin.company.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:180'],
            'nif' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:5000'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:190'],
            'currency' => ['required', 'string', 'size:3', 'alpha'],
            'delivery_fee' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
        ]);

        $settings = CompanySetting::query()->first();

        if (! $settings) {
            $settings = CompanySetting::query()->create([
                'company_name' => 'GPM',
                'currency' => 'AOA',
                'delivery_fee' => 0,
            ]);
        }

        $settings->update([
            ...$validated,
            'company_name' => trim($validated['company_name']),
            'currency' => strtoupper($validated['currency']),
        ]);

        ActivityLog::query()->create([
            'user_id' => Auth::guard('web')->id(),
            'action' => 'company_settings_updated',
            'entity_type' => 'company_settings',
            'entity_id' => $settings->id,
            'description' => 'Dados comerciais da empresa atualizados.',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.company.edit')
            ->with('success', 'Dados da empresa guardados com sucesso.');
    }
}
