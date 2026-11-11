@extends('layouts.admin')

@section('title', 'Consulta a fornecedor')

@section('content')
<div class="min-h-screen lg:grid lg:grid-cols-[250px_1fr]">
    <aside class="bg-gpm-navy text-white p-6"><a href="{{ route('admin.dashboard') }}" class="font-black text-2xl">GPM<span class="text-gpm-orange">.</span></a><p class="text-[10px] uppercase tracking-[.2em] text-white/45 mt-2">Painel interno</p><nav class="mt-10 space-y-2 text-sm"><a href="{{ route('admin.requests.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Pedidos de fornecimento</a><a href="{{ route('admin.suppliers.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Fornecedores</a></nav><form method="post" action="{{ route('admin.logout') }}" class="mt-10">@csrf<button class="text-xs font-bold uppercase text-white/60">Terminar sessão</button></form></aside>
    <main class="p-5 md:p-8 lg:p-10">
        <a href="{{ route('admin.requests.show',$inquiry->quoteRequest) }}" class="text-xs text-gpm-orange font-bold uppercase"><i class="fa-solid fa-arrow-left mr-2"></i>Voltar ao pedido {{ $inquiry->quoteRequest->request_number }}</a>
        <div class="mt-4 mb-7"><span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Consulta a fornecedor</span><h1 class="text-3xl font-black text-gpm-navy mt-2">{{ $inquiry->supplier->name }}</h1><p class="text-sm text-gray-500 mt-2">Estado: {{ $inquiry->status }} · Criada em {{ optional($inquiry->created_at)->format('d/m/Y H:i') }}</p></div>
        @if(session('success'))<div class="bg-green-50 text-green-800 text-sm p-4 mb-6">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="bg-red-50 text-red-700 text-sm p-4 mb-6">{{ $errors->first() }}</div>@endif
        <div class="grid xl:grid-cols-[1fr_360px] gap-6 items-start">
            <div class="space-y-6">
                <section class="bg-white border border-gray-100 p-6"><h2 class="font-black text-gpm-navy mb-4">Dados da consulta</h2><div class="grid sm:grid-cols-2 gap-4 text-sm"><div><span class="block text-xs text-gray-400">Contacto</span>{{ $inquiry->supplier->contact_name ?: '—' }}</div><div><span class="block text-xs text-gray-400">Email</span>{{ $inquiry->supplier->email ?: '—' }}</div><div><span class="block text-xs text-gray-400">Telefone</span>{{ $inquiry->supplier->phone ?: '—' }}</div><div><span class="block text-xs text-gray-400">Referência</span>{{ $inquiry->reference ?: '—' }}</div></div>@if($inquiry->message)<p class="text-sm text-gray-600 whitespace-pre-line mt-5">{{ $inquiry->message }}</p>@endif</section>
                <section class="bg-white border border-gray-100 p-6"><h2 class="font-black text-gpm-navy mb-4">Artigos consultados</h2>@foreach($inquiry->quoteRequest->items as $item)<div class="flex justify-between gap-4 py-3 border-b border-gray-100"><div><strong class="text-sm text-gpm-navy">{{ $item->product_name }}</strong><p class="text-xs text-gray-400 mt-1">{{ $item->product_sku ?: 'Sem referência' }} · {{ $item->unit }}</p>@if($item->notes)<p class="text-xs text-gray-500 mt-1">{{ $item->notes }}</p>@endif</div><strong class="text-sm">{{ number_format((float)$item->quantity,3,',',' ') }} {{ $item->unit }}</strong></div>@endforeach</section>
                <section class="bg-white border border-gray-100 p-6"><h2 class="font-black text-gpm-navy mb-5">Respostas recebidas</h2>@forelse($inquiry->responses->sortByDesc('received_at') as $response)<div class="border border-gray-100 p-4 mb-4"><div class="flex justify-between gap-3"><strong class="text-sm text-gpm-navy">{{ $response->supplier_reference ?: 'Resposta sem referência' }}</strong><span class="text-[10px] uppercase font-bold text-gpm-orange">{{ $response->status }}</span></div><p class="text-xs text-gray-400 mt-2">Recebida {{ optional($response->received_at)->format('d/m/Y H:i') }} · Moeda: {{ $response->currency ?: 'Não indicada' }}</p>@foreach($response->items as $responseItem)<div class="border-t border-gray-100 mt-3 pt-3 flex justify-between gap-3 text-xs"><span>{{ $responseItem->quoteRequestItem->product_name }} · {{ $responseItem->availability_status }}</span><strong>{{ $responseItem->unit_price !== null ? number_format((float)$responseItem->unit_price,2,',','.') : 'Sem preço' }}</strong></div>@if($responseItem->conditions)<p class="text-xs text-gray-500 mt-1">{{ $responseItem->conditions }}</p>@endif @endforeach @if($response->lead_time)<p class="text-xs text-gray-500 mt-3">Prazo: {{ $response->lead_time }}</p>@endif @if($response->delivery_terms)<p class="text-xs text-gray-500 mt-1">Entrega: {{ $response->delivery_terms }}</p>@endif @if($response->payment_terms)<p class="text-xs text-gray-500 mt-1">Pagamento: {{ $response->payment_terms }}</p>@endif @if($response->notes)<p class="text-xs text-gray-500 mt-2">{{ $response->notes }}</p>@endif</div>@empty<p class="text-sm text-gray-400">Ainda não foi registada uma resposta.</p>@endforelse</section>
            </div>
            <aside class="space-y-6">
                @if($inquiry->status === 'draft')
                    <section class="bg-white border border-gray-100 p-6"><h2 class="font-black text-gpm-navy">Marcar como enviada</h2><p class="text-xs text-gray-500 mt-2">Esta ação regista que a equipa enviou a consulta por um canal externo. Nenhum email é enviado automaticamente.</p><form method="post" action="{{ route('admin.supplier-inquiries.mark-sent',$inquiry) }}">@csrf @method('PATCH')<button class="w-full bg-gpm-orange text-white text-xs font-bold uppercase py-4 mt-5">Marcar consulta enviada</button></form></section>
                @endif
                @if(in_array($inquiry->status,['sent','responded'],true))
                    <section class="bg-white border border-gray-100 p-6"><h2 class="font-black text-gpm-navy mb-4">Registar resposta</h2><form method="post" action="{{ route('admin.supplier-inquiries.responses.store',$inquiry) }}">@csrf
                        <label class="block text-xs font-bold text-gpm-navy">Referência do fornecedor<input name="supplier_reference" maxlength="100" class="w-full border-gray-200 text-sm mt-2"></label>
                        <label class="block text-xs font-bold text-gpm-navy mt-3">Moeda (ISO 4217)<input name="currency" maxlength="3" placeholder="AOA, USD, EUR..." class="w-full border-gray-200 text-sm mt-2"></label>
                        <label class="block text-xs font-bold text-gpm-navy mt-3">Prazo geral<input name="lead_time" maxlength="180" class="w-full border-gray-200 text-sm mt-2"></label>
                        <label class="block text-xs font-bold text-gpm-navy mt-3">Condições de entrega<textarea name="delivery_terms" rows="2" class="w-full border-gray-200 text-sm mt-2"></textarea></label>
                        <label class="block text-xs font-bold text-gpm-navy mt-3">Condições de pagamento<textarea name="payment_terms" rows="2" class="w-full border-gray-200 text-sm mt-2"></textarea></label>
                        <label class="block text-xs font-bold text-gpm-navy mt-3">Validade da cotação<input type="date" name="valid_until" class="w-full border-gray-200 text-sm mt-2"></label>
                        <div class="border-t border-gray-100 mt-5 pt-5"><h3 class="font-bold text-sm text-gpm-navy mb-4">Resposta por artigo</h3>
                            @foreach($inquiry->quoteRequest->items as $item)
                                <div class="border border-gray-100 p-3 mb-3"><strong class="text-xs text-gpm-navy">{{ $item->product_name }}</strong><input type="hidden" name="items[{{ $loop->index }}][quote_request_item_id]" value="{{ $item->id }}">
                                    <label class="block text-[10px] uppercase font-bold text-gray-400 mt-3">Preço unitário<input type="number" name="items[{{ $loop->index }}][unit_price]" min="0" step="0.01" class="w-full border-gray-200 text-sm mt-1"></label>
                                    <label class="block text-[10px] uppercase font-bold text-gray-400 mt-3">Disponibilidade<select name="items[{{ $loop->index }}][availability_status]" required class="w-full border-gray-200 text-sm mt-1"><option value="unknown">Por confirmar</option><option value="available">Disponível</option><option value="partial">Parcial</option><option value="unavailable">Indisponível</option><option value="substitute">Alternativa</option></select></label>
                                    <label class="block text-[10px] uppercase font-bold text-gray-400 mt-3">Quantidade disponível<input type="number" name="items[{{ $loop->index }}][available_quantity]" min="0" step="0.001" class="w-full border-gray-200 text-sm mt-1"></label>
                                    <label class="block text-[10px] uppercase font-bold text-gray-400 mt-3">Prazo específico<input name="items[{{ $loop->index }}][lead_time]" maxlength="180" class="w-full border-gray-200 text-sm mt-1"></label>
                                    <label class="block text-[10px] uppercase font-bold text-gray-400 mt-3">Condições / notas<textarea name="items[{{ $loop->index }}][conditions]" rows="2" class="w-full border-gray-200 text-sm mt-1"></textarea></label>
                                </div>
                            @endforeach
                        </div>
                        <label class="block text-xs font-bold text-gpm-navy">Notas da resposta<textarea name="notes" rows="3" class="w-full border-gray-200 text-sm mt-2"></textarea></label>
                        <button class="w-full bg-gpm-navy text-white text-xs font-bold uppercase py-4 mt-5">Guardar resposta</button>
                    </form></section>
                @endif
            </aside>
        </div>
    </main>
</div>
@endsection
