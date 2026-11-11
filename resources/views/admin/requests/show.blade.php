@extends('layouts.admin')

@section('title', 'Analisar '.$quoteRequest->request_number)

@section('content')
@php($currentStatus = \App\Enums\QuoteRequestStatus::tryFrom($quoteRequest->status))
<div class="min-h-screen lg:grid lg:grid-cols-[250px_1fr]">
    <aside class="bg-gpm-navy text-white p-6"><a href="{{ route('admin.dashboard') }}" class="font-black text-2xl">GPM<span class="text-gpm-orange">.</span></a><p class="text-[10px] uppercase tracking-[.2em] text-white/45 mt-2">Painel interno</p><nav class="mt-10 space-y-2 text-sm"><a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Visão geral</a><a href="{{ route('admin.requests.index') }}" class="block px-4 py-3 bg-white/10 text-white">Pedidos de fornecimento</a><a href="{{ route('admin.suppliers.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Fornecedores</a></nav><form method="post" action="{{ route('admin.logout') }}" class="mt-10">@csrf<button class="text-xs font-bold uppercase text-white/60">Terminar sessão</button></form></aside>
    <main class="p-5 md:p-8 lg:p-10">
        <a href="{{ route('admin.requests.index') }}" class="text-xs text-gpm-orange font-bold uppercase"><i class="fa-solid fa-arrow-left mr-2"></i>Voltar aos pedidos</a>
        <div class="mt-4 mb-7"><span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Pedido de fornecimento</span><h1 class="text-3xl font-black text-gpm-navy mt-2">{{ $quoteRequest->request_number }}</h1><p class="text-sm text-gray-500 mt-2">Estado: {{ $currentStatus?->label() ?? $quoteRequest->status }} · Enviado em {{ optional($quoteRequest->submitted_at)->format('d/m/Y H:i') }}</p></div>
        @if(session('success'))<div class="bg-green-50 text-green-800 text-sm p-4 mb-6">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="bg-red-50 text-red-700 text-sm p-4 mb-6">{{ $errors->first() }}</div>@endif
        <div class="grid xl:grid-cols-[1fr_340px] gap-6 items-start">
            <div class="space-y-6">
                <section class="bg-white border border-gray-100 p-6"><h2 class="font-black text-gpm-navy mb-5">Lista solicitada</h2><div class="space-y-4">@foreach($quoteRequest->items as $item)<div class="border-b border-gray-100 pb-4 flex justify-between gap-4"><div><strong class="text-sm text-gpm-navy">{{ $item->product_name }}</strong><p class="text-xs text-gray-400 mt-1">{{ $item->product_sku ?: 'Sem SKU' }} · {{ $item->unit }}</p>@if($item->notes)<p class="text-xs text-gray-500 mt-2">Observação: {{ $item->notes }}</p>@endif</div><strong class="text-sm whitespace-nowrap">{{ number_format((float)$item->quantity,3,',',' ') }} {{ $item->unit }}</strong></div>@endforeach</div>@if($quoteRequest->notes)<div class="mt-6"><h3 class="text-xs font-bold uppercase text-gray-400">Observações gerais</h3><p class="text-sm text-gray-600 mt-2 whitespace-pre-line">{{ $quoteRequest->notes }}</p></div>@endif</section>
                <section class="bg-white border border-gray-100 p-6"><h2 class="font-black text-gpm-navy mb-5">Histórico de alterações</h2>@forelse($quoteRequest->statusHistory->sortBy('created_at') as $event)<div class="border-l-2 border-gpm-orange pl-4 mb-5"><strong class="text-sm text-gpm-navy">{{ $event->old_status ?: '—' }} → {{ $event->new_status }}</strong><p class="text-xs text-gray-400 mt-1">{{ optional($event->created_at)->format('d/m/Y H:i') }} · {{ $event->user?->name ?: 'Utilizador removido' }}</p>@if($event->note)<p class="text-sm text-gray-500 mt-2">{{ $event->note }}</p>@endif</div>@empty<p class="text-sm text-gray-400">Ainda não existem alterações de estado.</p>@endforelse</section>
                <section class="bg-white border border-gray-100 p-6">
                    <h2 class="font-black text-gpm-navy mb-5">Consultas a fornecedores</h2>
                    @forelse($quoteRequest->supplierInquiries as $inquiry)
                        <a href="{{ route('admin.supplier-inquiries.show',$inquiry) }}" class="block border border-gray-100 p-4 mb-3 hover:border-gpm-orange">
                            <div class="flex justify-between gap-3"><strong class="text-sm text-gpm-navy">{{ $inquiry->supplier->name }}</strong><span class="text-[10px] uppercase font-bold text-gpm-orange">{{ $inquiry->status }}</span></div>
                            <p class="text-xs text-gray-400 mt-2">Criada {{ optional($inquiry->created_at)->format('d/m/Y H:i') }} · {{ $inquiry->responses->count() }} resposta(s)</p>
                        </a>
                    @empty
                        <p class="text-sm text-gray-400">Ainda não foram criadas consultas a fornecedores.</p>
                    @endforelse
                    @if(in_array($quoteRequest->status,['under_review','supplier_research'],true))
                        <form method="post" action="{{ route('admin.requests.inquiries.store',$quoteRequest) }}" class="border-t border-gray-100 pt-5 mt-5">
                            @csrf
                            <h3 class="font-bold text-sm text-gpm-navy mb-4">Criar nova consulta</h3>
                            <label class="block text-xs font-bold text-gpm-navy">Fornecedor<select name="supplier_id" required class="w-full border-gray-200 text-sm mt-2"><option value="">Escolha um fornecedor</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>@endforeach</select></label>
                            <label class="block text-xs font-bold text-gpm-navy mt-3">Referência interna<input name="reference" maxlength="80" class="w-full border-gray-200 text-sm mt-2"></label>
                            <label class="block text-xs font-bold text-gpm-navy mt-3">Mensagem / especificações<textarea name="message" rows="3" maxlength="5000" class="w-full border-gray-200 text-sm mt-2"></textarea></label>
                            <label class="block text-xs font-bold text-gpm-navy mt-3">Prazo de resposta pretendido<input type="date" name="response_due_at" min="{{ now()->toDateString() }}" class="w-full border-gray-200 text-sm mt-2"></label>
                            <button class="w-full bg-gpm-navy text-white text-xs font-bold uppercase py-4 mt-4">Criar consulta</button>
                        </form>
                    @else
                        <p class="text-[11px] text-gray-400 mt-4">Coloque o pedido em análise ou consulta a fornecedores para iniciar novas consultas.</p>
                    @endif
                </section>
            </div>
            <aside class="space-y-6">
                <section class="bg-white border border-gray-100 p-6"><h2 class="font-black text-gpm-navy mb-4">Cliente</h2><p class="font-bold text-sm">{{ $quoteRequest->customer_name }}</p><p class="text-sm text-gray-500 mt-2">{{ $quoteRequest->customer_email }}</p><p class="text-sm text-gray-500">{{ $quoteRequest->customer_phone }}</p><a href="mailto:{{ $quoteRequest->customer_email }}" class="block bg-gpm-navy text-white text-center text-xs font-bold uppercase py-3 mt-6">Contactar cliente</a></section>
                <section class="bg-white border border-gray-100 p-6"><h2 class="font-black text-gpm-navy mb-4">Atualizar etapa</h2>
                    <form method="post" action="{{ route('admin.requests.status.update',$quoteRequest) }}">@csrf @method('PATCH')
                        <label class="block text-xs font-bold text-gpm-navy">Novo estado<select name="status" required class="w-full border-gray-200 text-sm mt-2">@foreach($statuses as $statusOption)<option value="{{ $statusOption->value }}" @selected($quoteRequest->status===$statusOption->value)>{{ $statusOption->label() }}</option>@endforeach</select></label>
                        <label class="block text-xs font-bold text-gpm-navy mt-4">Nota do histórico<textarea name="note" rows="4" maxlength="5000" placeholder="Registe o motivo ou o resultado desta etapa..." class="w-full border-gray-200 text-sm mt-2"></textarea></label>
                        <button class="w-full bg-gpm-orange text-white text-xs font-bold uppercase py-4 mt-4">Guardar etapa</button>
                    </form>
                    <p class="text-[11px] text-gray-400 mt-4 leading-relaxed">O sistema bloqueia saltos de etapa não permitidos e regista o responsável e a data de cada alteração.</p>
                </section>
                <div class="bg-gpm-soft p-4"><strong class="text-xs text-gpm-navy">Regra comercial</strong><p class="text-xs text-gray-500 mt-2 leading-relaxed">A proposta será criada numa etapa própria. A aceitação do cliente não confirma automaticamente uma encomenda.</p></div>
            </aside>
        </div>
    </main>
</div>
@endsection
