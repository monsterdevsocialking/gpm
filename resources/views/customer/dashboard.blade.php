@extends('layouts.app')

@section('title', 'Área do cliente')

@section('content')
<header class="bg-white border-b border-gray-100"><div class="max-w-site mx-auto px-6 h-20 flex items-center justify-between"><a href="{{ route('home') }}" class="font-black text-2xl text-gpm-navy">GPM<span class="text-gpm-orange">.</span></a><div class="flex items-center gap-4"><span class="hidden sm:inline text-xs text-gray-500">{{ $customer->name }}</span><form method="post" action="{{ route('customer.logout') }}">@csrf<button class="text-xs font-bold uppercase text-gpm-orange">Sair <i class="fa-solid fa-arrow-right-from-bracket ml-1"></i></button></form></div></div></header>
<main class="max-w-site mx-auto px-6 py-10">
    <div class="flex flex-col md:flex-row justify-between md:items-end gap-4 mb-8"><div><span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Portal empresarial</span><h1 class="text-3xl md:text-4xl font-black text-gpm-navy mt-2">Olá, {{ $customer->name }}.</h1><p class="text-sm text-gray-500 mt-2">Consulte o histórico de pedidos associados à sua conta.</p></div><a href="{{ route('marketplace.index') }}" class="bg-gpm-orange text-white text-xs font-bold uppercase px-6 py-4">Explorar marketplace</a></div>
    @if(session('success'))<div class="bg-green-50 text-green-800 p-4 text-sm mb-6">{{ session('success') }}</div>@endif
    <div class="grid sm:grid-cols-2 gap-4 mb-8"><div class="bg-white border border-gray-100 p-6"><span class="text-xs text-gray-500">Pedidos registados</span><strong class="block text-3xl font-black text-gpm-navy mt-2">{{ $totalOrders }}</strong></div><div class="bg-white border border-gray-100 p-6"><span class="text-xs text-gray-500">Conta</span><strong class="block text-lg font-black text-gpm-navy mt-2">{{ $customer->customer_type === 'company' ? 'Empresa' : 'Particular' }}</strong></div></div>
    <section class="bg-white border border-gray-100">
        <div class="p-5 border-b border-gray-100"><h2 class="font-black text-gpm-navy">Os meus pedidos</h2></div>
        @if($orders->isEmpty())<div class="p-10 text-center"><i class="fa-solid fa-clipboard-list text-gray-300 text-4xl"></i><p class="text-sm text-gray-500 mt-4">Ainda não existem pedidos associados a esta conta.</p><a href="{{ route('marketplace.index') }}" class="inline-block text-gpm-orange font-bold text-xs uppercase mt-5">Ver catálogo</a></div>
        @else
        <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr><th class="text-left p-4">Referência</th><th class="text-left p-4">Data</th><th class="text-left p-4">Artigos</th><th class="text-left p-4">Total legado</th><th class="text-left p-4">Estado legado</th></tr></thead><tbody>
            @foreach($orders as $order)<tr class="border-t border-gray-100"><td class="p-4 font-bold"><a class="text-gpm-navy hover:text-gpm-orange" href="{{ route('customer.orders.show', $order) }}">{{ $order->order_number }}</a></td><td class="p-4 text-gray-500">{{ optional($order->created_at)->format('d/m/Y') }}</td><td class="p-4 text-gray-500">{{ $order->items->count() }}</td><td class="p-4 text-gray-500">{{ number_format((float)$order->total, 2, ',', '.') }} Kz</td><td class="p-4 text-gray-500">{{ $order->status }}</td></tr>@endforeach
        </tbody></table></div>
        @endif
        <p class="text-[11px] text-gray-400 p-5 border-t border-gray-100">Os pedidos acima pertencem ao histórico do sistema anterior. O novo fluxo de pedidos de fornecimento e propostas será ativado após a migração comercial e validação dos dados.</p>
    </section>
</main>
@endsection
