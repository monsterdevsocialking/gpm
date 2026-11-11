@extends('layouts.admin')

@section('title', 'Pedidos')

@section('content')
<div class="min-h-screen lg:grid lg:grid-cols-[250px_1fr]">
    <aside class="bg-gpm-navy text-white p-6"><a href="{{ route('admin.dashboard') }}" class="font-black text-2xl">GPM<span class="text-gpm-orange">.</span></a><p class="text-[10px] uppercase tracking-[.2em] text-white/45 mt-2">Painel interno</p><nav class="mt-10 space-y-2 text-sm"><a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Visão geral</a><a href="{{ route('admin.orders.index') }}" class="block px-4 py-3 bg-white/10 text-white"><i class="fa-solid fa-clipboard-list text-gpm-orange mr-2"></i>Pedidos</a><a href="{{ route('marketplace.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Marketplace</a></nav><form method="post" action="{{ route('admin.logout') }}" class="mt-10">@csrf<button class="text-xs font-bold uppercase text-white/60">Terminar sessão</button></form></aside>
    <main class="p-5 md:p-8 lg:p-10">
        <div class="mb-7"><a href="{{ route('admin.dashboard') }}" class="text-xs text-gray-400">Painel</a><h1 class="text-3xl font-black text-gpm-navy mt-2">Pedidos</h1><p class="text-sm text-gray-500 mt-1">Consulta e acompanha as encomendas confirmadas pela equipa GPM.</p></div>
        <form method="get" class="bg-white border border-gray-100 p-4 flex flex-col sm:flex-row gap-3 mb-5"><input name="q" value="{{ $search }}" placeholder="Referência, cliente, telefone ou NIF" class="flex-1 border-gray-200 text-sm"><select name="status" class="border-gray-200 text-sm"><option value="">Todos os estados</option>@foreach($allowedStatuses as $item)<option value="{{ $item }}" @selected($status===$item)>{{ ucfirst($item) }}</option>@endforeach</select><button class="bg-gpm-navy text-white text-xs font-bold uppercase px-6 py-3">Filtrar</button></form>
        <section class="bg-white border border-gray-100 overflow-x-auto"><table class="w-full text-sm"><thead class="bg-gray-50 text-[10px] uppercase text-gray-500"><tr><th class="text-left p-4">Referência</th><th class="text-left p-4">Cliente</th><th class="text-left p-4">Contacto</th><th class="text-left p-4">Total</th><th class="text-left p-4">Estado</th><th class="text-left p-4">Data</th><th></th></tr></thead><tbody>
            @forelse($orders as $order)<tr class="border-t border-gray-100"><td class="p-4 font-bold text-gpm-navy">{{ $order->order_number }}</td><td class="p-4">{{ $order->customer_name }}</td><td class="p-4 text-gray-500">{{ $order->customer_phone }}</td><td class="p-4">{{ number_format((float)$order->total,2,',','.') }} {{ $order->currency ?? 'AOA' }}</td><td class="p-4">{{ $order->status }}</td><td class="p-4 text-gray-400">{{ optional($order->created_at)->format('d/m/Y') }}</td><td class="p-4"><a href="{{ route('admin.orders.show',$order) }}" class="text-gpm-orange font-bold text-xs uppercase">Abrir</a></td></tr>@empty<tr><td colspan="7" class="p-10 text-center text-gray-400">Nenhum pedido encontrado.</td></tr>@endforelse
        </tbody></table></section>
        <div class="mt-6">{{ $orders->links() }}</div>
    </main>
</div>
@endsection
