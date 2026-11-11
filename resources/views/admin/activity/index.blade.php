@extends('layouts.admin')

@section('title', 'Registo de atividade')

@section('content')
<div class="min-h-screen lg:grid lg:grid-cols-[250px_1fr]">
    <aside class="bg-gpm-navy text-white p-6"><a href="{{ route('admin.dashboard') }}" class="font-black text-2xl">GPM<span class="text-gpm-orange">.</span></a><p class="text-[10px] uppercase tracking-[.2em] text-white/45 mt-2">Painel interno</p><nav class="mt-10 space-y-2 text-sm"><a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Visão geral</a><a href="{{ route('admin.users.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Utilizadores</a><a href="{{ route('admin.activity.index') }}" class="block px-4 py-3 bg-white/10 text-white"><i class="fa-solid fa-clock-rotate-left text-gpm-orange mr-2"></i>Registo de atividade</a></nav><form method="post" action="{{ route('admin.logout') }}" class="mt-10">@csrf<button class="text-xs font-bold uppercase text-white/60">Terminar sessão</button></form></aside>
    <main class="p-5 md:p-8 lg:p-10">
        <div class="mb-7"><span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Auditoria interna</span><h1 class="text-3xl font-black text-gpm-navy mt-2">Registo de atividade</h1><p class="text-sm text-gray-500 mt-1">Histórico de ações administrativas e alterações relevantes.</p></div>
        <form method="get" class="bg-white border border-gray-100 p-4 flex flex-col sm:flex-row gap-3 mb-5"><input name="q" value="{{ $search }}" placeholder="Descrição, tipo de registo ou ID" class="flex-1 border-gray-200 text-sm"><select name="action" class="border-gray-200 text-sm"><option value="">Todas as ações</option>@foreach($actions as $actionName)<option value="{{ $actionName }}" @selected($action===$actionName)>{{ $actionName }}</option>@endforeach</select><button class="bg-gpm-navy text-white text-xs font-bold uppercase px-6 py-3">Filtrar</button></form>
        <section class="bg-white border border-gray-100 overflow-x-auto"><table class="w-full text-sm"><thead class="bg-gray-50 text-[10px] uppercase text-gray-500"><tr><th class="text-left p-4">Data</th><th class="text-left p-4">Responsável</th><th class="text-left p-4">Ação</th><th class="text-left p-4">Registo</th><th class="text-left p-4">Descrição</th><th class="text-left p-4">IP</th></tr></thead><tbody>
            @forelse($logs as $log)
                <tr class="border-t border-gray-100 align-top"><td class="p-4 whitespace-nowrap text-gray-400">{{ optional($log->created_at)->format('d/m/Y H:i:s') }}</td><td class="p-4"><strong class="text-gpm-navy">{{ $log->user?->name ?: 'Sistema / utilizador removido' }}</strong></td><td class="p-4"><span class="text-[10px] uppercase font-bold text-gpm-orange">{{ $log->action }}</span></td><td class="p-4 text-gray-500">{{ $log->entity_type ?: '—' }} {{ $log->entity_id ? '#'.$log->entity_id : '' }}</td><td class="p-4 min-w-[260px] whitespace-pre-line">{{ $log->description ?: '—' }}</td><td class="p-4 text-xs text-gray-400">{{ $log->ip_address ?: '—' }}</td></tr>
            @empty<tr><td colspan="6" class="p-10 text-center text-gray-400">Ainda não existem registos de atividade.</td></tr>@endforelse
        </tbody></table><div class="p-5">{{ $logs->links() }}</div></section>
    </main>
</div>
@endsection
