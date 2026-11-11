<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $role = (string) $request->query('role', '');

        $users = User::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role !== '', fn ($query) => $query->where('role', $role))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'role'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(12)],
            'role' => ['required', Rule::in(['admin', 'seller', 'stock_manager'])],
        ]);

        $user = User::query()->create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'],
        ]);

        $user->forceFill([
            'role' => $validated['role'],
            'active' => true,
        ])->save();

        ActivityLog::query()->create([
            'user_id' => Auth::guard('web')->id(),
            'action' => 'staff_created',
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'description' => 'Conta interna criada com o perfil '.$user->role.'.',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilizador interno criado com sucesso.');
    }

    public function toggleActive(Request $request, int $user): RedirectResponse
    {
        $record = User::query()->findOrFail($user);
        $currentUserId = Auth::guard('web')->id();

        if ($record->id === $currentUserId) {
            return back()->withErrors(['user' => 'Não pode desativar a sua própria conta enquanto está autenticado.']);
        }

        if ($record->role === 'admin' && $record->active) {
            $activeAdmins = User::query()
                ->where('role', 'admin')
                ->where('active', true)
                ->count();

            if ($activeAdmins <= 1) {
                return back()->withErrors(['user' => 'Não pode desativar o último administrador ativo.']);
            }
        }

        $newState = ! $record->active;
        $record->forceFill(['active' => $newState])->save();

        ActivityLog::query()->create([
            'user_id' => $currentUserId,
            'action' => $newState ? 'staff_activated' : 'staff_deactivated',
            'entity_type' => 'user',
            'entity_id' => $record->id,
            'description' => 'Estado da conta interna alterado para '.($newState ? 'ativo' : 'inativo').'.',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Estado do utilizador atualizado.');
    }
}
