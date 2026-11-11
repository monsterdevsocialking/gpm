@extends('layouts.app')

@section('title', 'Conversa da encomenda '.$conversation->order?->order_number)

@section('content')
<header class="bg-white border-b border-gray-100"><div class="max-w-site mx-auto px-6 h-20 flex items-center justify-between"><a href="{{ route('home') }}" class="font-black text-2xl text-gpm-navy">GPM<span class="text-gpm-orange">.</span></a><a href="{{ route('customer.orders.show',$conversation->order) }}" class="text-xs font-bold uppercase text-gpm-navy"><i class="fa-solid fa-arrow-left mr-2"></i>Voltar à encomenda</a></div></header>
<main class="max-w-4xl mx-auto px-5 py-10">
    <span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Atendimento GPM</span><h1 class="text-3xl font-black text-gpm-navy mt-2">Conversa da encomenda</h1><p class="text-sm text-gray-500 mt-2">{{ $conversation->order?->order_number }} · {{ $conversation->status==='open' ? 'Aberta' : 'Fechada' }}</p>
    @if(session('success'))<div class="bg-green-50 text-green-800 text-sm p-4 mt-5">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 text-red-700 text-sm p-4 mt-5">{{ $errors->first() }}</div>@endif
    <section class="bg-white border border-gray-100 mt-6">
        <div class="bg-slate-50 p-5 md:p-7 space-y-4 min-h-[360px] max-h-[600px] overflow-y-auto">
            @forelse($conversation->messages->sortBy('created_at') as $message)
                <div class="flex {{ $message->sender_type==='customer' ? 'justify-end' : 'justify-start' }}"><article class="max-w-[85%] p-4 {{ $message->sender_type==='customer' ? 'bg-gpm-navy text-white' : 'bg-white border border-gray-100 text-gray-700' }}"><strong class="block text-[10px] uppercase tracking-wider {{ $message->sender_type==='customer' ? 'text-gpm-orange' : 'text-gray-400' }}">{{ $message->sender_type==='customer' ? 'Você' : ($message->userSender?->name ?: 'Equipa GPM') }}</strong><p class="text-sm whitespace-pre-line mt-2">{{ $message->body }}</p><span class="block text-[10px] mt-3 {{ $message->sender_type==='customer' ? 'text-white/45' : 'text-gray-400' }}">{{ optional($message->created_at)->format('d/m/Y H:i') }}</span></article></div>
            @empty
                <div class="text-center py-16"><i class="fa-solid fa-comments text-gray-200 text-4xl"></i><p class="text-sm text-gray-400 mt-4">Ainda não existem mensagens. Envie a sua primeira mensagem à equipa GPM.</p></div>
            @endforelse
        </div>
        @if($conversation->status==='open')
            <form method="post" action="{{ route('customer.conversations.messages.store',$conversation) }}" class="p-5 md:p-7">@csrf<label class="block text-xs font-bold text-gpm-navy">A sua mensagem<textarea name="body" rows="4" maxlength="5000" required class="w-full border-gray-200 text-sm mt-2" placeholder="Escreva aqui a sua mensagem..."></textarea></label><div class="flex justify-end mt-4"><button class="bg-gpm-orange text-white text-xs font-bold uppercase px-7 py-4">Enviar mensagem</button></div></form>
        @else
            <p class="p-5 text-sm text-gray-500">Esta conversa foi fechada pela equipa GPM. Se precisar de novo atendimento, contacte-nos através do site.</p>
        @endif
    </section>
</main>
@endsection
