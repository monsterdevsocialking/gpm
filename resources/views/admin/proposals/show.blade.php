@extends('layouts.admin')

@section('title', 'Proposta '.$version->proposal->proposal_number)

@section('content')
<div class="min-h-screen lg:grid lg:grid-cols-[250px_1fr]">
    <aside class="bg-gpm-navy text-white p-6"><a href="{{ route('admin.dashboard') }}" class="font-black text-2xl">GPM<span class="text-gpm-orange">.</span></a><p class="text-[10px] uppercase tracking-[.2em] text-white/45 mt-2">Painel interno</p><nav class="mt-10 space-y-2 text-sm"><a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Visão geral</a><a href="{{ route('admin.requests.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Pedidos de fornecimento</a><a href="{{ route('admin.suppliers.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Fornecedores</a></nav><form method="post" action="{{ route('admin.logout') }}" class="mt-10">@csrf<button class="text-xs font-bold uppercase text-white/60">Terminar sessão</button></form></aside>
    <main class="p-5 md:p-8 lg:p-10">
        <a href="{{ route('admin.requests.show',$version->proposal->quoteRequest) }}" class="text-xs text-gpm-orange font-bold uppercase"><i class="fa-solid fa-arrow-left mr-2"></i>Voltar ao pedido {{ $version->proposal->quoteRequest->request_number }}</a>
        <div class="mt-4 mb-7"><span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Proposta comercial · Versão {{ $version->version_number }}</span><h1 class="text-3xl font-black text-gpm-navy mt-2">{{ $version->proposal->proposal_number }}</h1><p class="text-sm text-gray-500 mt-2">Cliente: {{ $version->proposal->quoteRequest->customer_name }} · Estado: {{ $version->status }}</p></div>
        @if(session('success'))<div class="bg-green-50 text-green-800 text-sm p-4 mb-6">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="bg-red-50 text-red-700 text-sm p-4 mb-6">{{ $errors->first() }}</div>@endif
        <div class="grid xl:grid-cols-[1fr_330px] gap-6 items-start">
            <section class="bg-white border border-gray-100 p-6 md:p-8">
                <h2 class="font-black text-gpm-navy mb-2">Artigos e preços</h2><p class="text-xs text-gray-400 mb-6">Os nomes, quantidades e unidades são copiados do pedido original. Os totais são calculados no servidor.</p>
                @if($version->isEditable())
                    <form method="post" action="{{ route('admin.proposals.update',$version) }}">@csrf @method('PUT')
                        <div class="grid sm:grid-cols-2 gap-4 mb-6">
                            <label class="text-xs font-bold text-gpm-navy">Moeda<select name="currency" class="w-full border-gray-200 text-sm mt-2"><option value="">Selecionar moeda</option>@foreach(['AOA'=>'Kwanza (AOA)','USD'=>'Dólar (USD)','EUR'=>'Euro (EUR)'] as $code=>$label)<option value="{{ $code }}" @selected(old('currency',$version->currency)===$code)>{{ $label }}</option>@endforeach</select></label>
                            <label class="text-xs font-bold text-gpm-navy">Proposta válida até<input type="date" name="valid_until" min="{{ today()->toDateString() }}" value="{{ old('valid_until',optional($version->valid_until)->format('Y-m-d')) }}" class="w-full border-gray-200 text-sm mt-2"></label>
                        </div>
                        <div class="space-y-4">
                            @foreach($version->items as $item)
                                <div class="border border-gray-100 p-4">
                                    <div class="flex justify-between gap-4"><div><strong class="text-sm text-gpm-navy">{{ $item->product_name }}</strong><p class="text-xs text-gray-400 mt-1">{{ $item->product_sku ?: 'Sem SKU' }} · {{ $item->unit }}</p></div><strong class="text-sm whitespace-nowrap">{{ number_format((float)$item->quantity,3,',',' ') }} {{ $item->unit }}</strong></div>
                                    <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                    <div class="grid sm:grid-cols-2 gap-3 mt-4">
                                        <label class="text-[10px] font-bold uppercase text-gray-400">Preço unitário<input type="number" name="items[{{ $loop->index }}][unit_price]" min="0" step="0.01" value="{{ old('items.'.$loop->index.'.unit_price',$item->unit_price) }}" class="w-full border-gray-200 text-sm mt-1"></label>
                                        <label class="text-[10px] font-bold uppercase text-gray-400">Observação do artigo<input name="items[{{ $loop->index }}][notes]" maxlength="2000" value="{{ old('items.'.$loop->index.'.notes',$item->notes) }}" class="w-full border-gray-200 text-sm mt-1"></label>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-3">Subtotal da linha: {{ $item->line_total !== null ? number_format((float)$item->line_total,2,',','.') : 'Será calculado ao guardar' }}</p>
                                </div>
                            @endforeach
                        </div>
                        <label class="block text-xs font-bold text-gpm-navy mt-6">Condições comerciais<textarea name="terms" rows="5" maxlength="10000" class="w-full border-gray-200 text-sm mt-2" placeholder="Indique condições de entrega, pagamento e outras condições acordadas.">{{ old('terms',$version->terms) }}</textarea></label>
                        <label class="block text-xs font-bold text-gpm-navy mt-4">Notas internas / adicionais<textarea name="notes" rows="3" maxlength="5000" class="w-full border-gray-200 text-sm mt-2">{{ old('notes',$version->notes) }}</textarea></label>
                        <button class="bg-gpm-navy text-white text-xs font-bold uppercase px-7 py-4 mt-6">Guardar rascunho</button>
                    </form>
                    <form method="post" action="{{ route('admin.proposals.issue',$version) }}" class="border-t border-gray-100 mt-7 pt-6" onsubmit="return confirm('Emitir esta versão? Depois da emissão, os valores e artigos ficam bloqueados.');">@csrf<button class="bg-gpm-orange text-white text-xs font-bold uppercase px-7 py-4">Emitir proposta ao cliente</button><p class="text-[11px] text-gray-400 mt-3">A emissão bloqueia esta versão. Para alterações futuras será criada uma nova versão.</p></form>
                @else
                    <div class="flex flex-wrap justify-between gap-3 mb-6"><div><p class="text-xs text-gray-400">Moeda</p><strong>{{ $version->currency }}</strong></div><div><p class="text-xs text-gray-400">Validade</p><strong>{{ optional($version->valid_until)->format('d/m/Y') ?: '—' }}</strong></div><div><p class="text-xs text-gray-400">Emitida em</p><strong>{{ optional($version->issued_at)->format('d/m/Y H:i') }}</strong></div></div>
                    @foreach($version->items as $item)<div class="flex justify-between gap-4 py-4 border-b border-gray-100"><div><strong class="text-sm text-gpm-navy">{{ $item->product_name }}</strong><p class="text-xs text-gray-400 mt-1">{{ number_format((float)$item->quantity,3,',',' ') }} {{ $item->unit }} × {{ number_format((float)$item->unit_price,2,',','.') }} {{ $version->currency }}</p>@if($item->notes)<p class="text-xs text-gray-500 mt-1">{{ $item->notes }}</p>@endif</div><strong class="text-sm">{{ number_format((float)$item->line_total,2,',','.') }} {{ $version->currency }}</strong></div>@endforeach
                    <div class="text-right mt-6"><span class="text-xs text-gray-400 uppercase">Subtotal</span><strong class="block text-2xl font-black text-gpm-navy">{{ number_format((float)$version->subtotal,2,',','.') }} {{ $version->currency }}</strong></div>
                    @if($version->terms)<div class="border-t border-gray-100 mt-6 pt-5"><h3 class="text-xs font-bold uppercase text-gray-400">Condições comerciais</h3><p class="text-sm text-gray-600 whitespace-pre-line mt-2">{{ $version->terms }}</p></div>@endif
                @endif
            </section>
            <aside class="space-y-6">
                <section class="bg-white border border-gray-100 p-6"><h2 class="font-black text-gpm-navy mb-4">Histórico de decisões</h2>@forelse($version->decisions as $decision)<div class="border-l-2 border-gpm-orange pl-4 mb-4"><strong class="text-sm text-gpm-navy">{{ ['accepted'=>'Aceite','rejected'=>'Rejeitada','revision_requested'=>'Alterações solicitadas'][$decision->decision] ?? $decision->decision }}</strong><p class="text-xs text-gray-400 mt-1">{{ optional($decision->decided_at)->format('d/m/Y H:i') }}</p>@if($decision->comment)<p class="text-xs text-gray-600 mt-2">{{ $decision->comment }}</p>@endif</div>@empty<p class="text-sm text-gray-400">Ainda não existe uma decisão do cliente para esta versão.</p>@endforelse</section>
                @if($version->order)
                    <section class="bg-white border border-emerald-100 p-6">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-emerald-700">Encomenda confirmada</span>
                        <h2 class="font-black text-gpm-navy mt-2">{{ $version->order->order_number }}</h2>
                        <a href="{{ route('admin.orders.show',$version->order) }}" class="block bg-gpm-navy text-white text-xs font-bold uppercase text-center py-3 mt-4">Abrir encomenda</a>
                    </section>
                @elseif($version->status === 'sent' && $version->decisions->contains(fn ($decision) => $decision->decision === 'accepted'))
                    <section class="bg-white border border-gpm-orange/30 p-6">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-gpm-orange">Aceitação recebida</span>
                        <h2 class="font-black text-gpm-navy mt-2">Confirmar encomenda</h2>
                        <p class="text-xs text-gray-500 mt-2">A proposta foi aceite pelo cliente. Esta ação cria a encomenda e regista o funcionário responsável. Não inicia pagamentos nem desconta stock.</p>
                        <form method="post" action="{{ route('admin.proposals.confirm-order',$version) }}" class="mt-4" onsubmit="return confirm('Confirmar agora a encomenda a partir desta proposta aceite?');">@csrf<button class="w-full bg-gpm-orange text-white text-xs font-bold uppercase py-4">Confirmar encomenda</button></form>
                    </section>
                @endif
                <div class="bg-gpm-soft p-5"><strong class="text-xs text-gpm-navy">Confirmação da encomenda</strong><p class="text-xs text-gray-500 leading-relaxed mt-2">A aceitação do cliente é apenas uma decisão comercial. A encomenda só poderá ser confirmada por um funcionário GPM autorizado num passo separado.</p></div>
            </aside>
        </div>
    </main>
</div>
@endsection
