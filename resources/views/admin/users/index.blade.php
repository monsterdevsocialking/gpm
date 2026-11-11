@extends('layouts.admin')

@section('title', 'Utilizadores internos')

@section('content')
<div class="min-h-screen lg:grid lg:grid-cols-[250px_1fr]">
    <aside class="bg-gpm-navy text-white p-6"><a href="{{ route('admin.dashboard') }}" class="font-black text-2xl">GPM<span class="text-gpm-orange">.</span></a><p class="text-[10px] uppercase tracking-[.2em] text-white/45 mt-2">Painel interno</p><nav class="mt-10 space-y-2 text-sm"><a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Visão geral</a><a href="{{ route('admin.catalog.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Produtos</a><a href="{{ route('admin.categories.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Categorias</a><a href="{{ route('admin.stock.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Movimentos de stock</a><a href="{{ route('admin.users.index') }}" class="block px-4 py-3 bg-white/10 text-white"><i class="fa-solid fa-user-shield text-gpm-orange mr-2"></i>Utilizadores</a><a href="{{ route('admin.company.edit') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Dados da empresa</a></nav><form method="post" action="{{ route('admin.logout') }}" class="mt-10">@csrf<button class="text-xs font-bold uppercase text-white/60">Terminar sessão</button></form></aside>
    <main class="p-5 md:p-8 lg:p-10">
        <div class="mb-7"><span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Segurança e equipa</span><h1 class="text-3xl font-black text-gpm-navy mt-2">Utilizadores internos</h1><p class="text-sm text-gray-500 mt-1">Crie contas de equipa e controle o acesso sem apagar o histórico de atividade.</p></div>
        @if(session('success'))<div class="bg-green-50 text-green-800 text-sm p-4 mb-6">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="bg-red-50 text-red-700 text-sm p-4 mb-6">{{ $errors->first() }}</div>@endif
        <section class="bg-white border border-gray-100 p-6 mb-7">
            <h2 class="font-black text-gpm-navy mb-5">Criar utilizador interno</h2>
            <form method="post" action="{{ route('admin.users.store') }}">@csrf
                <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    <label class="text-xs font-bold text-gpm-navy">Nome completo<input name="name" required maxlength="150" value="{{ old('name') }}" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy">Email<input type="email" name="email" required maxlength="190" value="{{ old('email') }}" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy">Palavra-passe inicial<input type="password" name="password" required minlength="12" autocomplete="new-password" class="w-full border-gray-200 text-sm mt-2"><span class="text-[10px] text-gray-400">Mínimo de 12 caracteres.</span></label>
                    <label class="text-xs font-bold text-gpm-navy">Confirmar palavra-passe<input type="password" name="password_confirmation" required minlength="12" autocomplete="new-password" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy sm:col-span-2">Perfil<select name="role" required class="w-full border-gray-200 text-sm mt-2"><option value="seller" @selected(old('role')==='seller')>Vendedor</option><option value="stock_manager" @selected(old('role')==='stock_manager')>Gestor de stock</option><option value="admin" @selected(old('role')==='admin')>Administrador</option></select></label>
                </div>
                <button class="bg-gpm-orange text-white text-xs font-bold uppercase px-7 py-4 mt-5">Criar conta interna</button>
            </form>
        </section>
        <section class="bg-white border border-gray-100">
            <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3"><h2 class="font-black text-gpm-navy">Contas registadas</h2><form method="get" class="flex gap-2"><input name="q" value="{{ $search }}" placeholder="Nome ou email" class="border-gray-200 text-sm"><select name="role" class="border-gray-200 text-sm"><option value="">Todos os perfis</option>@foreach(['admin'=>'Administrador','seller'=>'Vendedor','stock_manager'=>'Gestor de stock'] as $key=>$label)<option value="{{ $key }}" @selected($role===$key)>{{ $label }}</option>@endforeach</select><button class="bg-gpm-navy text-white text-xs font-bold uppercase px-5">Filtrar</button></form></div>
            <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-gray-50 text-[10px] uppercase text-gray-500"><tr><th class="text-left p-4">Utilizador</th><th class="text-left p-4">Perfil</th><th class="text-left p-4">Último acesso</th><th class="text-left p-4">Estado</th><th class="text-right p-4">Ação</th></tr></thead><tbody>
                @forelse($users as $user)
                    <tr class="border-t border-gray-100"><td class="p-4"><strong class="text-gpm-navy">{{ $user->name }}</strong><p class="text-xs text-gray-400">{{ $user->email }}</p></td><td class="p-4">{{ ['admin'=>'Administrador','seller'=>'Vendedor','stock_manager'=>'Gestor de stock'][$user->role] ?? $user->role }}</td><td class="p-4 text-gray-400">{{ optional($user->last_login_at)->format('d/m/Y H:i') ?: 'Nunca' }}</td><td class="p-4"><span class="text-[10px] font-bold uppercase {{ $user->active ? 'text-emerald-700' : 'text-red-600' }}">{{ $user->active ? 'Ativo' : 'Inativo' }}</span></td><td class="p-4 text-right">@if($user->id !== auth()->id())<form method="post" action="{{ route('admin.users.toggle-active',$user) }}">@csrf @method('PATCH')<button class="text-xs font-bold uppercase {{ $user->active ? 'text-red-600' : 'text-emerald-700' }}">{{ $user->active ? 'Desativar' : 'Ativar' }}</button></form>@else<span class="text-[10px] text-gray-400">Sessão atual</span>@endif</td></tr>
                @empty<tr><td colspan="5" class="p-8 text-center text-gray-400">Nenhum utilizador encontrado.</td></tr>@endforelse
            </tbody></table></div>
            <div class="p-5">{{ $users->links() }}</div>
        </section>
    </main>
</div>
@endsection
