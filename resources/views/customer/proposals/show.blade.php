@extends('layouts.app')

@section('title', 'Proposta '.$version->proposal->proposal_number)

@section('content')
<header class="bg-white border-b border-gray-100"><div class="max-w-site mx-auto px-6 h-20 flex items-center justify-between"><a href="{{ route('home') }}" class="font-black text-2xl text-gpm-navy">GPM<span class="text-gpm-orange">.</span></a><a href="{{ route('customer.dashboard') }}" class="text-xs font-bold uppercase text-gpm-navy">A minha conta</a></div></header>
<main class="max-w-4xl mx-auto px-5 py-10 md:py-14">
    <a href="{{ route('customer.requests.show',$version->proposal->quoteRequest) }}" class="text-xs font-bold uppercase text-gpm-orange"><i class="fa-solid fa-arrow-left mr-2"></i>Voltar ao pedido</a>
    <section class="bg-white border border-gray-100 p-6 md:p-10 mt-5">
        <span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Proposta comercial · Versão {{ $version->version_number }}</span>
        <h1 class="text-2xl md:text-3xl font-black text-gpm-navy mt-3">{{ $version->proposal->proposal_number }}</h1>
        <p class="text-sm text-gray-500 mt-2">Referência do pedido: {{ $version->proposal->quoteRequest->request_number }}</p>
        @if(session('success'))<div class="bg-green-50 text-green-800 text-sm p-4 mt-6">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="bg-red-50 text-red-700 text-sm p-4 mt-6">{{ $errors->first() }}</div>@endif
        <div class="grid sm:grid-cols-3 gap-4 bg-gpm-soft p-5 mt-7"><div><span class="block text-[10px] uppercase text-gray-400">Estado</span><strong class="text-sm text-gpm-navy">{{ $version->status === 'sent' ? 'Emitida' : $version->status }}</strong></div><div><span class="block text-[10px] uppercase text-gray-400">Emitida em</span><strong class="text-sm text-gpm-navy">{{ optional($version->issued_at)->format('d/m/Y') }}</strong></div><div><span class="block text-[10px] uppercase text-gray-400">Válida até</span><strong class="text-sm text-gpm-navy">{{ optional($version->valid_until)->format('d/m/Y') ?: '—' }}</strong></div></div>
        <div class="mt-8"><h2 class="font-black text-gpm-navy mb-4">Artigos e valores</h2>@foreach($version->items as $item)<div class="flex justify-between gap-4 py-4 border-b border-gray-100"><div><strong class="text-sm text-gpm-navy">{{ $item->product_name }}</strong><p class="text-xs text-gray-400 mt-1">{{ number_format((float)$item->quantity,3,',',' ') }} {{ $item->unit }} × {{ number_format((float)$item->unit_price,2,',','.') }} {{ $version->currency }}</p>@if($item->notes)<p class="text-xs text-gray-500 mt-2">{{ $item->notes }}</p>@endif</div><strong class="text-sm text-gpm-navy whitespace-nowrap">{{ number_format((float)$item->line_total,2,',','.') }} {{ $version->currency }}</strong></div>@endforeach</div>
        <div class="text-right mt-6"><span class="text-xs text-gray-400 uppercase">Subtotal</span><strong class="block text-2xl font-black text-gpm-navy">{{ number_format((float)$version->subtotal,2,',','.') }} {{ $version->currency }}</strong></div>
        @if($version->terms)<div class="border-t border-gray-100 mt-7 pt-6"><h2 class="text-xs font-bold uppercase text-gray-400">Condições comerciais</h2><p class="text-sm text-gray-600 whitespace-pre-line mt-3">{{ $version->terms }}</p></div>@endif
        @if($version->notes)<div class="border-t border-gray-100 mt-6 pt-6"><h2 class="text-xs font-bold uppercase text-gray-400">Notas adicionais</h2><p class="text-sm text-gray-600 whitespace-pre-line mt-3">{{ $version->notes }}</p></div>@endif
        @php($decision = $version->decisions->first())
        @if($decision)
            <div class="border border-gray-100 p-5 mt-8"><span class="text-[10px] uppercase tracking-wider text-gray-400">A sua decisão</span><h2 class="font-black text-gpm-navy mt-2">{{ ['accepted'=>'Proposta aceite','rejected'=>'Proposta rejeitada','revision_requested'=>'Alterações solicitadas'][$decision->decision] ?? $decision->decision }}</h2><p class="text-xs text-gray-400 mt-1">{{ optional($decision->decided_at)->format('d/m/Y H:i') }}</p>@if($decision->comment)<p class="text-sm text-gray-600 mt-3 whitespace-pre-line">{{ $decision->comment }}</p>@endif</div>
        @elseif($version->valid_until && $version->valid_until->gte(today()))
            <div class="border-t border-gray-100 mt-8 pt-7"><h2 class="font-black text-gpm-navy">Responder à proposta</h2><p class="text-sm text-gray-500 mt-2">Analise os valores e condições. A sua decisão será registada e comunicada à equipa GPM.</p>
                <form method="post" action="{{ route('customer.proposals.decide',$version) }}" class="mt-5">@csrf
                    <label class="block text-xs font-bold text-gpm-navy">Comentário (opcional)<textarea name="comment" rows="3" maxlength="5000" class="w-full border-gray-200 text-sm mt-2" placeholder="Escreva alguma observação ou condição..."></textarea></label>
                    <div class="grid sm:grid-cols-3 gap-3 mt-5">
                        <button name="decision" value="accepted" class="bg-gpm-orange text-white text-xs font-bold uppercase py-4">Aceitar proposta</button>
                        <button name="decision" value="revision_requested" class="bg-gpm-navy text-white text-xs font-bold uppercase py-4">Pedir alterações</button>
                        <button name="decision" value="rejected" class="border border-gray-200 text-gray-600 text-xs font-bold uppercase py-4">Rejeitar proposta</button>
                    </div>
                </form>
            </div>
        @else
            <div class="bg-amber-50 text-amber-800 text-sm p-5 mt-8">A validade desta proposta terminou. Contacte a GPM para solicitar uma nova versão.</div>
        @endif
        <div class="bg-gpm-soft p-5 mt-8"><strong class="text-xs text-gpm-navy">Importante</strong><p class="text-xs text-gray-500 leading-relaxed mt-2">A aceitação desta proposta não confirma automaticamente uma encomenda nem inicia um pagamento. A GPM irá rever a sua decisão e um funcionário autorizado confirmará a encomenda numa etapa separada.</p></div>
    </section>
</main>
@endsection
