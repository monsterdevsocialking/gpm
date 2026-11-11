@extends('layouts.admin')

@section('title', 'Mensagens de contacto')

@section('content')
<div class="min-h-screen lg:grid lg:grid-cols-[250px_1fr]">
    <aside class="bg-gpm-navy text-white p-6"><a href="{{ route('admin.dashboard') }}" class="font-black text-2xl">GPM<span class="text-gpm-orange">.</span></a><p class="text-[10px] uppercase tracking-[.2em] text-white/45 mt-2">Painel interno</p><nav class="mt-10 space-y-2 text-sm"><a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Visão geral</a><a href="{{ route('admin.customers.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Clientes</a><a href="{{ route('admin.requests.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Pedidos de fornecimento</a><a href="{{ route('admin.contacts.index') }}" class="block px-4 py-3 bg-white/10 text-white"><i class="fa-solid fa-envelope text-gpm-orange mr-2"></i>Mensagens de contacto</a></nav><form method="post" action="{{ route('admin.logout') }}" class="mt-10">@csrf<button class="text-xs font-bold uppercase text-white/60">Terminar sessão</button></form></aside>
    <main class="p-5 md:p-8 lg:p-10">
        <div class="mb-7"><span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Atendimento</span><h1 class="text-3xl font-black text-gpm-navy mt-2">Mensagens de contacto</h1><p class="text-sm text-gray-500 mt-1">Mensagens enviadas através do formulário público do site.</p></div>
        <section class="bg-white border border-gray-100 overflow-x-auto"><div class="p-5 border-b border-gray-100"><h2 class="font-black text-gpm-navy">{{ $messages->total() }} mensagem(ns)</h2></div><table class="w-full text-sm"><thead class="bg-gray-50 text-[10px] uppercase text-gray-500"><tr><th class="text-left p-4">Data</th><th class="text-left p-4">Nome</th><th class="text-left p-4">Contacto</th><th class="text-left p-4">Assunto</th><th class="text-left p-4">Mensagem</th></tr></thead><tbody>
            @forelse($messages as $message)
                <tr class="border-t border-gray-100 align-top"><td class="p-4 text-gray-400 whitespace-nowrap">{{ optional($message->created_at)->format('d/m/Y H:i') }}</td><td class="p-4 font-bold text-gpm-navy">{{ $message->name }}</td><td class="p-4"><a href="mailto:{{ $message->email }}" class="text-gpm-orange">{{ $message->email }}</a>@if($message->phone)<p class="text-xs text-gray-400 mt-1">{{ $message->phone }}</p>@endif</td><td class="p-4">{{ $message->subject ?: '—' }}</td><td class="p-4 whitespace-pre-line min-w-[260px]">{{ $message->message }}</td></tr>
            @empty<tr><td colspan="5" class="p-10 text-center text-gray-400">Ainda não existem mensagens.</td></tr>@endforelse
        </tbody></table><div class="p-5">{{ $messages->links() }}</div></section>
    </main>
</div>
@endsection
