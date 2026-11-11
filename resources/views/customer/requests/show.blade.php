@extends('layouts.app')

@section('title', 'Pedido '.$quoteRequest->request_number)

@section('content')
<header class="bg-white border-b border-gray-100"><div class="max-w-site mx-auto px-6 h-20 flex items-center justify-between"><a href="{{ route('home') }}" class="font-black text-2xl text-gpm-navy">GPM<span class="text-gpm-orange">.</span></a><a href="{{ route('customer.dashboard') }}" class="text-xs font-bold uppercase text-gpm-navy">A minha conta</a></div></header>
<main class="max-w-4xl mx-auto px-5 py-12">
    <div class="bg-white border border-gray-100 p-7 md:p-10">
        <span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Pedido de fornecimento enviado</span>
        <h1 class="text-2xl md:text-3xl font-black text-gpm-navy mt-3">{{ $quoteRequest->request_number }}</h1>
        @if(session('success'))<div class="bg-green-50 text-green-800 text-sm p-4 mt-5">{{ session('success') }}</div>@endif
        <div class="grid sm:grid-cols-2 gap-4 mt-7 text-sm"><div><span class="block text-xs text-gray-400">Estado</span><strong class="text-gpm-navy">{{ $quoteRequest->status }}</strong></div><div><span class="block text-xs text-gray-400">Data de envio</span><strong class="text-gpm-navy">{{ optional($quoteRequest->submitted_at)->format('d/m/Y H:i') }}</strong></div></div>
        <div class="border-t border-gray-100 mt-7 pt-6"><h2 class="font-black text-gpm-navy mb-4">Artigos solicitados</h2><div class="space-y-4">@foreach($quoteRequest->items as $item)<div class="flex justify-between gap-4 border-b border-gray-100 pb-4"><div><strong class="text-sm text-gpm-navy">{{ $item->product_name }}</strong><p class="text-xs text-gray-400 mt-1">{{ $item->product_sku ?: 'Sem referência' }} · {{ $item->unit }}</p>@if($item->notes)<p class="text-xs text-gray-500 mt-2">{{ $item->notes }}</p>@endif</div><strong class="text-sm text-gpm-navy whitespace-nowrap">{{ number_format((float)$item->quantity,3,',',' ') }} {{ $item->unit }}</strong></div>@endforeach</div></div>
        @if($quoteRequest->notes)<div class="border-t border-gray-100 mt-6 pt-6"><h2 class="text-xs font-bold uppercase text-gray-400">Observações gerais</h2><p class="text-sm text-gray-600 mt-2 whitespace-pre-line">{{ $quoteRequest->notes }}</p></div>@endif
        <div class="bg-gpm-soft p-5 mt-7"><h2 class="font-bold text-sm text-gpm-navy">O que acontece agora?</h2><p class="text-xs text-gray-500 leading-relaxed mt-2">A equipa GPM irá analisar os artigos e as quantidades, consultar disponibilidade e fornecedores e, quando estiver preparada, apresentar uma proposta comercial na sua área de cliente. Este pedido ainda não é uma encomenda confirmada.</p></div>
        <a href="{{ route('customer.dashboard') }}" class="inline-block bg-gpm-navy text-white font-bold text-xs uppercase px-7 py-4 mt-7">Voltar à minha conta</a>
    </div>
</main>
@endsection
