@extends('layouts.admin')

@section('title', 'Pedido '.$order->order_number)

@section('content')
<div class="min-h-screen lg:grid lg:grid-cols-[250px_1fr]">
    <aside class="bg-gpm-navy text-white p-6"><a href="{{ route('admin.dashboard') }}" class="font-black text-2xl">GPM<span class="text-gpm-orange">.</span></a><p class="text-[10px] uppercase tracking-[.2em] text-white/45 mt-2">Painel interno</p><nav class="mt-10 space-y-2 text-sm"><a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Visão geral</a><a href="{{ route('admin.orders.index') }}" class="block px-4 py-3 bg-white/10 text-white">Pedidos</a></nav><form method="post" action="{{ route('admin.logout') }}" class="mt-10">@csrf<button class="text-xs font-bold uppercase text-white/60">Terminar sessão</button></form></aside>
    <main class="p-5 md:p-8 lg:p-10">
        <a href="{{ route('admin.orders.index') }}" class="text-xs text-gpm-orange font-bold uppercase"><i class="fa-solid fa-arrow-left mr-2"></i>Voltar aos pedidos</a>
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mt-4 mb-7"><div><span class="text-[10px] uppercase tracking-[.2em] text-gpm-orange font-extrabold">Encomenda</span><h1 class="text-3xl font-black text-gpm-navy mt-2">{{ $order->order_number }}</h1></div><span class="bg-white border border-gray-200 px-4 py-2 text-xs font-bold uppercase">{{ $order->status }}</span></div>
        <div class="grid xl:grid-cols-[1fr_330px] gap-6 items-start">
            <div class="space-y-6">
                <section class="bg-white border border-gray-100 p-6"><h2 class="font-black text-gpm-navy mb-4">Dados do cliente</h2><div class="grid sm:grid-cols-2 gap-4 text-sm"><div><span class="text-xs text-gray-400 block">Nome</span>{{ $order->customer_name }}</div><div><span class="text-xs text-gray-400 block">Telefone</span>{{ $order->customer_phone }}</div><div><span class="text-xs text-gray-400 block">Email</span>{{ $order->customer_email ?: '—' }}</div><div><span class="text-xs text-gray-400 block">NIF</span>{{ $order->customer_nif ?: '—' }}</div><div class="sm:col-span-2"><span class="text-xs text-gray-400 block">Morada de entrega</span>{{ $order->delivery_address ?: '—' }}</div></div></section>
                @if($order->proposalVersion)<section class="bg-white border border-gpm-orange/30 p-5"><span class="text-[10px] uppercase font-bold text-gpm-orange">Origem comercial</span><h2 class="font-black text-gpm-navy mt-2">{{ $order->proposalVersion->proposal->proposal_number }} · Versão {{ $order->proposalVersion->version_number }}</h2><a href="{{ route('admin.proposals.show',$order->proposalVersion) }}" class="text-xs text-gpm-orange font-bold uppercase inline-block mt-3">Consultar proposta</a></section>@endif
                <section class="bg-white border border-gray-100 p-6"><h2 class="font-black text-gpm-navy mb-4">Artigos</h2><div class="overflow-x-auto"><table class="w-full text-sm"><thead class="text-[10px] uppercase text-gray-400"><tr><th class="text-left py-2">Produto</th><th class="text-right py-2">Quantidade</th><th class="text-right py-2">Preço</th><th class="text-right py-2">Subtotal</th></tr></thead><tbody>@foreach($order->items as $item)<tr class="border-t border-gray-100"><td class="py-3">{{ $item->product_name }}<div class="text-xs text-gray-400">{{ $item->product_sku }}</div></td><td class="text-right py-3">{{ $item->quantity }} {{ $item->unit }}</td><td class="text-right py-3">{{ number_format((float)$item->unit_price,2,',','.') }} {{ $order->currency ?? 'AOA' }}</td><td class="text-right py-3 font-bold">{{ number_format((float)$item->subtotal,2,',','.') }} {{ $order->currency ?? 'AOA' }}</td></tr>@endforeach</tbody></table></div></section>
                @if($order->statusHistory->isNotEmpty())<section class="bg-white border border-gray-100 p-6"><h2 class="font-black text-gpm-navy mb-4">Histórico de estado</h2>@foreach($order->statusHistory->sortBy('created_at') as $event)<div class="border-l-2 border-gpm-orange pl-4 mb-4"><strong class="text-sm">{{ $event->new_status }}</strong><p class="text-xs text-gray-400">{{ optional($event->created_at)->format('d/m/Y H:i') }} · {{ $event->user?->name ?: 'Sistema/cliente' }}</p>@if($event->note)<p class="text-sm text-gray-500 mt-1">{{ $event->note }}</p>@endif</div>@endforeach</section>@endif
            </div>
            <aside class="bg-white border border-gray-100 p-6"><h2 class="font-black text-gpm-navy mb-4">Resumo</h2><div class="flex justify-between text-sm py-2"><span class="text-gray-500">Subtotal</span>{{ number_format((float)$order->subtotal,2,',','.') }} {{ $order->currency ?? 'AOA' }}</div><div class="flex justify-between text-sm py-2"><span class="text-gray-500">Entrega</span>{{ number_format((float)$order->delivery_fee,2,',','.') }} {{ $order->currency ?? 'AOA' }}</div><div class="flex justify-between text-sm py-2"><span class="text-gray-500">Desconto</span>{{ number_format((float)$order->discount,2,',','.') }} {{ $order->currency ?? 'AOA' }}</div><div class="border-t border-gray-100 mt-3 pt-4 flex justify-between font-black text-gpm-navy"><span>Total</span><span>{{ number_format((float)$order->total,2,',','.') }} {{ $order->currency ?? 'AOA' }}</span></div>@php($nextStatuses = match ($order->status) { 'pending' => ['confirmed' => 'Confirmar encomenda', 'cancelled' => 'Cancelar'], 'confirmed' => ['preparing' => 'Iniciar preparação', 'cancelled' => 'Cancelar'], 'preparing' => ['shipped' => 'Marcar como expedida', 'cancelled' => 'Cancelar'], 'shipped' => ['completed' => 'Marcar como concluída'], default => [] })
                <div class="border-t border-gray-100 mt-6 pt-5">
                    <h3 class="font-bold text-sm text-gpm-navy mb-3">Acompanhamento da encomenda</h3>
                    @if($nextStatuses)
                        <form method="post" action="{{ route('admin.orders.status.update',$order) }}">@csrf @method('PATCH')
                            <label class="block text-xs font-bold text-gpm-navy">Próxima etapa<select name="status" required class="w-full border-gray-200 text-sm mt-2">@foreach($nextStatuses as $statusValue=>$statusLabel)<option value="{{ $statusValue }}">{{ $statusLabel }}</option>@endforeach</select></label>
                            <label class="block text-xs font-bold text-gpm-navy mt-3">Nota do acompanhamento<textarea name="note" rows="2" maxlength="5000" class="w-full border-gray-200 text-sm mt-2" placeholder="Opcional: transportadora, referência ou observação..."></textarea></label>
                            <button class="w-full bg-gpm-orange text-white text-xs font-bold uppercase py-3 mt-4">Atualizar estado</button>
                        </form>
                    @else
                        <p class="text-xs text-gray-400">Esta encomenda já se encontra num estado final.</p>
                    @endif
                    <p class="text-[11px] text-gray-400 mt-3">As alterações ficam registadas no histórico. O acompanhamento não desconta stock nem inicia pagamentos.</p>
                </div>
                <div class="border-t border-gray-100 mt-6 pt-5">
                    <h3 class="font-bold text-sm text-gpm-navy mb-3">Proformas emitidas</h3>
                    @forelse($order->proformas->sortByDesc('id') as $proforma)
                        <a href="{{ route('admin.proformas.show',$proforma) }}" class="flex justify-between items-center text-xs py-3 border-b border-gray-100 hover:text-gpm-orange"><span>{{ $proforma->proforma_number }} · {{ $proforma->status }}</span><strong>{{ number_format((float)$proforma->total,2,',','.') }} {{ $proforma->currency ?? $order->currency ?? 'AOA' }}</strong></a>
                    @empty
                        <p class="text-xs text-gray-400">Ainda não foram emitidas proformas.</p>
                    @endforelse
                    @if(in_array($order->status,['confirmed','preparing','shipped','completed'],true))
                        <form method="post" action="{{ route('admin.orders.proformas.store',$order) }}" class="mt-5 border-t border-gray-100 pt-5" onsubmit="return confirm('Emitir uma proforma com os valores desta encomenda?');">@csrf
                            <h4 class="text-xs font-bold text-gpm-navy mb-3">Emitir nova proforma</h4>
                            <label class="block text-xs font-bold text-gpm-navy">Válida até (opcional)<input type="date" name="valid_until" min="{{ today()->toDateString() }}" class="w-full border-gray-200 text-sm mt-2"></label>
                            <label class="block text-xs font-bold text-gpm-navy mt-3">Observações<textarea name="notes" rows="2" maxlength="5000" class="w-full border-gray-200 text-sm mt-2"></textarea></label>
                            <button class="w-full bg-gpm-orange text-white text-xs font-bold uppercase py-3 mt-4">Emitir proforma</button>
                        </form>
                    @endif
                </div></aside>
        </div>
    </main>
</div>
@endsection
