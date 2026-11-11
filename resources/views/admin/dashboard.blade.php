@extends('layouts.admin')

@section('title', 'Visão geral')

@section('content')
<div class="min-h-screen lg:grid lg:grid-cols-[250px_1fr]">
    <aside class="bg-gpm-navy text-white p-6 lg:min-h-screen">
        <a href="{{ route('admin.dashboard') }}" class="font-black text-2xl tracking-tighter">GPM<span class="text-gpm-orange">.</span></a>
        <p class="text-[10px] uppercase tracking-[.2em] text-white/45 mt-2">Painel interno</p>
        <nav class="mt-10 space-y-2 text-sm">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 bg-white/10 px-4 py-3 text-white"><i class="fa-solid fa-chart-line w-4 text-gpm-orange"></i>Visão geral</a>
            @if($canReviewRequests)
                <a href="{{ route('admin.catalog.index') }}" class="flex items-center gap-3 px-4 py-3 text-white/70 hover:bg-white/10"><i class="fa-solid fa-box w-4"></i>Produtos</a>
                <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-4 py-3 text-white/70 hover:bg-white/10"><i class="fa-solid fa-layer-group w-4"></i>Categorias</a>
                <a href="{{ route('admin.requests.index') }}" class="flex items-center gap-3 px-4 py-3 text-white/70 hover:bg-white/10"><i class="fa-solid fa-inbox w-4"></i>Pedidos de fornecimento</a>
                <a href="{{ route('admin.suppliers.index') }}" class="flex items-center gap-3 px-4 py-3 text-white/70 hover:bg-white/10"><i class="fa-solid fa-truck-field w-4"></i>Fornecedores</a>
                <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-4 py-3 text-white/70 hover:bg-white/10"><i class="fa-solid fa-clipboard-list w-4"></i>Encomendas</a>
            @endif
            <a href="{{ route('marketplace.index') }}" class="flex items-center gap-3 px-4 py-3 text-white/70 hover:bg-white/10"><i class="fa-solid fa-store w-4"></i>Ver marketplace</a>
        </nav>
        <form method="post" action="{{ route('admin.logout') }}" class="mt-10">@csrf<button class="text-xs font-bold uppercase text-white/60 hover:text-white"><i class="fa-solid fa-arrow-right-from-bracket mr-2"></i>Terminar sessão</button></form>
    </aside>
    <main class="p-5 md:p-8 lg:p-10">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-8"><div><span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">GPM Conecta</span><h1 class="text-3xl font-black text-gpm-navy mt-2">Visão geral</h1><p class="text-sm text-gray-500 mt-1">Resumo da operação e atividade recente.</p></div><div class="text-xs text-gray-500">{{ auth()->user()->name }} · {{ auth()->user()->role }}</div></div>
        <section class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach([['Produtos',$stats['products'],'fa-box'],['Clientes',$stats['customers'],'fa-users'],['Pedidos antigos',$stats['orders'],'fa-clipboard-list'],['Pedidos pendentes',$stats['pending'],'fa-clock'],['Stock baixo',$stats['low_stock'],'fa-triangle-exclamation']] as $stat)
                <article class="bg-white border border-gray-100 p-5"><div class="flex justify-between items-center"><span class="text-xs text-gray-500">{{ $stat[0] }}</span><i class="fa-solid {{ $stat[2] }} text-gpm-orange"></i></div><strong class="block text-3xl font-black text-gpm-navy mt-4">{{ number_format($stat[1],0,',','.') }}</strong></article>
            @endforeach
            @if($canReviewRequests)
                <article class="bg-white border border-gray-100 p-5"><div class="flex justify-between items-center"><span class="text-xs text-gray-500">Fornecedores ativos</span><i class="fa-solid fa-truck-field text-gpm-orange"></i></div><strong class="block text-3xl font-black text-gpm-navy mt-4">{{ number_format($stats['suppliers'],0,',','.') }}</strong></article>
                <article class="bg-white border border-gray-100 p-5"><div class="flex justify-between items-center"><span class="text-xs text-gray-500">Pedidos de fornecimento</span><i class="fa-solid fa-inbox text-gpm-orange"></i></div><strong class="block text-3xl font-black text-gpm-navy mt-4">{{ number_format($stats['quote_requests'],0,',','.') }}</strong></article>
                <article class="bg-white border border-gray-100 p-5"><div class="flex justify-between items-center"><span class="text-xs text-gray-500">Por analisar</span><i class="fa-solid fa-hourglass-half text-gpm-orange"></i></div><strong class="block text-3xl font-black text-gpm-navy mt-4">{{ number_format($stats['submitted_requests'],0,',','.') }}</strong></article>
            @endif
        </section>
        @if($canReviewRequests)
            <section class="bg-white border border-gray-100 mt-8">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center"><h2 class="font-black text-gpm-navy">Pedidos de fornecimento recentes</h2><a href="{{ route('admin.requests.index') }}" class="text-xs font-bold uppercase text-gpm-orange">Ver todos</a></div>
                <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-gray-50 text-[10px] uppercase text-gray-500"><tr><th class="text-left p-4">Referência</th><th class="text-left p-4">Cliente</th><th class="text-left p-4">Artigos</th><th class="text-left p-4">Estado</th><th class="text-left p-4">Data</th></tr></thead><tbody>
                    @forelse($recentRequests as $item)<tr class="border-t border-gray-100"><td class="p-4 font-bold text-gpm-navy"><a href="{{ route('admin.requests.show',$item) }}">{{ $item->request_number }}</a></td><td class="p-4">{{ $item->customer_name }}</td><td class="p-4">{{ $item->items_count }}</td><td class="p-4">{{ $item->status }}</td><td class="p-4 text-gray-400">{{ optional($item->submitted_at)->format('d/m/Y H:i') }}</td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-sm text-gray-400">Ainda não existem pedidos de fornecimento.</td></tr>@endforelse
                </tbody></table></div>
            </section>
            <section class="bg-white border border-gray-100 mt-8">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center"><h2 class="font-black text-gpm-navy">Pedidos antigos</h2><a href="{{ route('admin.orders.index') }}" class="text-xs font-bold uppercase text-gpm-orange">Ver todos</a></div>
                <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-gray-50 text-[10px] uppercase text-gray-500"><tr><th class="text-left p-4">Referência</th><th class="text-left p-4">Cliente</th><th class="text-left p-4">Total</th><th class="text-left p-4">Estado</th><th class="text-left p-4">Data</th></tr></thead><tbody>
                    @forelse($recentOrders as $order)<tr class="border-t border-gray-100"><td class="p-4 font-bold text-gpm-navy"><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td><td class="p-4">{{ $order->customer_name }}</td><td class="p-4">{{ number_format((float)$order->total,2,',','.') }} {{ $order->currency ?? 'AOA' }}</td><td class="p-4">{{ $order->status }}</td><td class="p-4 text-gray-400">{{ optional($order->created_at)->format('d/m/Y H:i') }}</td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-sm text-gray-400">Ainda não existem pedidos.</td></tr>@endforelse
                </tbody></table></div>
            </section>
        @endif
    </main>
</div>
@endsection
