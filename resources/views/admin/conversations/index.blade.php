@extends('layouts.admin')

@section('title', 'Conversas')

@section('content')
<div class="min-h-screen lg:grid lg:grid-cols-[250px_1fr]">
    <aside class="bg-gpm-navy text-white p-6"><a href="{{ route('admin.dashboard') }}" class="font-black text-2xl">GPM<span class="text-gpm-orange">.</span></a><p class="text-[10px] uppercase tracking-[.2em] text-white/45 mt-2">Painel interno</p><nav class="mt-10 space-y-2 text-sm"><a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Visão geral</a><a href="{{ route('admin.requests.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Pedidos de fornecimento</a><a href="{{ route('admin.orders.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Encomendas</a><a href="{{ route('admin.conversations.index') }}" class="block px-4 py-3 bg-white/10 text-white"><i class="fa-solid fa-comments text-gpm-orange mr-2"></i>Conversas</a><a href="{{ route('admin.contacts.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Mensagens de contacto</a></nav><form method="post" action="{{ route('admin.logout') }}" class="mt-10">@csrf<button class="text-xs font-bold uppercase text-white/60">Terminar sessão</button></form></aside>
    <main class="p-5 md:p-8 lg:p-10">
        <div class="mb-7"><span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Atendimento ao cliente</span><h1 class="text-3xl font-black text-gpm-navy mt-2">Conversas</h1><p class="text-sm text-gray-500 mt-1">Mensagens ligadas às encomendas, com histórico e controlo de abertura.</p></div>
        @if(session('success'))<div class="bg-green-50 text-green-800 text-sm p-4 mb-5">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="bg-red-50 text-red-700 text-sm p-4 mb-5">{{ $errors->first() }}</div>@endif
        <div class="grid xl:grid-cols-[330px_1fr] gap-5 items-start">
            <section class="bg-white border border-gray-100">
                <div class="p-5 border-b border-gray-100"><h2 class="font-black text-gpm-navy">Caixa de entrada</h2></div>
                <div class="divide-y divide-gray-100">
                    @forelse($conversations as $item)
                        <a href="{{ route('admin.conversations.show',$item) }}" class="block p-4 hover:bg-gray-50 {{ $conversation?->id === $item->id ? 'bg-gpm-soft border-l-2 border-gpm-orange' : '' }}">
                            <div class="flex justify-between gap-2"><strong class="text-sm text-gpm-navy">{{ $item->customer?->name ?: 'Cliente' }}</strong>@if($item->unread_customer_messages_count)<span class="bg-gpm-orange text-white text-[9px] font-bold px-2 py-1">{{ $item->unread_customer_messages_count }}</span>@endif</div>
                            <p class="text-xs text-gray-400 mt-1">Encomenda {{ $item->order?->order_number ?: '#'.$item->order_id }}</p>
                            <div class="flex justify-between gap-2 mt-3"><span class="text-[10px] uppercase font-bold {{ $item->status==='open' ? 'text-emerald-700' : 'text-gray-400' }}">{{ $item->status==='open' ? 'Aberta' : 'Fechada' }}</span><span class="text-[10px] text-gray-400">{{ optional($item->last_message_at ?: $item->updated_at)->format('d/m H:i') }}</span></div>
                        </a>
                    @empty
                        <p class="p-8 text-sm text-gray-400 text-center">Ainda não existem conversas.</p>
                    @endforelse
                </div>
                <div class="p-4">{{ $conversations->links() }}</div>
            </section>
            <section class="bg-white border border-gray-100 min-h-[620px] flex flex-col">
                @if(!$conversation)
                    <div class="flex-1 flex flex-col items-center justify-center p-10 text-center"><i class="fa-solid fa-comments text-4xl text-gray-200"></i><h2 class="font-black text-gpm-navy mt-4">Selecione uma conversa</h2><p class="text-sm text-gray-400 mt-2">Escolha uma conversa na caixa de entrada para responder ao cliente.</p></div>
                @else
                    <div class="p-5 border-b border-gray-100 flex flex-wrap justify-between items-center gap-3"><div><h2 class="font-black text-gpm-navy">{{ $conversation->customer?->name ?: 'Cliente' }}</h2><p class="text-xs text-gray-400 mt-1">Encomenda {{ $conversation->order?->order_number ?: '#'.$conversation->order_id }} · {{ $conversation->status==='open' ? 'Conversa aberta' : 'Conversa fechada' }}</p></div><form method="post" action="{{ route('admin.conversations.toggle-status',$conversation) }}">@csrf @method('PATCH')<button class="border border-gray-200 text-xs font-bold uppercase px-4 py-3">{{ $conversation->status==='open' ? 'Fechar conversa' : 'Reabrir conversa' }}</button></form></div>
                    <div class="flex-1 p-5 md:p-7 space-y-4 max-h-[600px] overflow-y-auto bg-slate-50">
                        @forelse($conversation->messages->sortBy('created_at') as $message)
                            <div class="flex {{ $message->sender_type==='staff' ? 'justify-end' : 'justify-start' }}"><article class="max-w-[85%] md:max-w-[75%] p-4 {{ $message->sender_type==='staff' ? 'bg-gpm-navy text-white' : 'bg-white border border-gray-100 text-gray-700' }}"><strong class="block text-[10px] uppercase tracking-wider {{ $message->sender_type==='staff' ? 'text-gpm-orange' : 'text-gray-400' }}">{{ $message->sender_type==='staff' ? ($message->userSender?->name ?: 'Equipa GPM') : ($message->customerSender?->name ?: 'Cliente') }}</strong><p class="text-sm whitespace-pre-line mt-2">{{ $message->body }}</p><span class="block text-[10px] mt-3 {{ $message->sender_type==='staff' ? 'text-white/45' : 'text-gray-400' }}">{{ optional($message->created_at)->format('d/m/Y H:i') }}</span></article></div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-12">Ainda não existem mensagens nesta conversa.</p>
                        @endforelse
                    </div>
                    @if($conversation->status==='open')
                        <form method="post" action="{{ route('admin.conversations.messages.store',$conversation) }}" class="p-5 border-t border-gray-100">@csrf<label class="block text-xs font-bold text-gpm-navy">Responder ao cliente<textarea name="body" rows="3" maxlength="5000" required class="w-full border-gray-200 text-sm mt-2" placeholder="Escreva a sua resposta..."></textarea></label><div class="flex justify-end mt-3"><button class="bg-gpm-orange text-white text-xs font-bold uppercase px-7 py-3">Enviar mensagem</button></div></form>
                    @else
                        <p class="p-5 text-xs text-gray-400 border-t border-gray-100">A conversa está fechada. Reabra-a para enviar uma nova mensagem.</p>
                    @endif
                @endif
            </section>
        </div>
    </main>
</div>
@endsection
