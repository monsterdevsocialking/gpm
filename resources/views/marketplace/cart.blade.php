@extends('layouts.app')

@section('title', 'Pedido de fornecimento')

@section('content')
<header class="bg-white border-b border-gray-100"><div class="max-w-site mx-auto px-6 h-20 flex items-center justify-between"><a href="{{ route('home') }}" class="font-black text-2xl text-gpm-navy">GPM<span class="text-gpm-orange">.</span></a><a href="{{ route('marketplace.index') }}" class="text-xs font-bold uppercase text-gpm-navy"><i class="fa-solid fa-arrow-left mr-2"></i>Continuar a explorar</a></div></header>
<main class="max-w-site mx-auto px-6 py-10 md:py-14">
    <span class="text-[10px] font-extrabold uppercase tracking-[.2em] text-gpm-orange">Marketplace GPM</span><h1 class="text-3xl md:text-4xl font-black text-gpm-navy mt-2">O seu pedido de fornecimento</h1><p class="text-sm text-gray-500 mt-3 max-w-2xl">Organize os produtos, quantidades e observações. A equipa GPM irá analisar o pedido e preparar uma resposta comercial. Este envio não confirma uma encomenda nem representa uma cobrança.</p>
    @if(session('success'))<div class="bg-green-50 text-green-800 text-sm p-4 mt-6">{{ session('success') }}</div>@endif
    @if(session('status'))<div class="bg-blue-50 text-blue-800 text-sm p-4 mt-6">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 text-red-700 text-sm p-4 mt-6">{{ $errors->first() }}</div>@endif
    @if($items->isEmpty())
        <div class="bg-white border border-gray-100 text-center py-16 mt-8"><i class="fa-solid fa-clipboard-list text-gray-300 text-5xl"></i><h2 class="font-black text-gpm-navy text-xl mt-5">Ainda não adicionou produtos</h2><p class="text-sm text-gray-500 mt-2">Explore o catálogo e adicione os artigos de que precisa.</p><a href="{{ route('marketplace.index') }}" class="inline-block bg-gpm-orange text-white text-xs font-bold uppercase px-7 py-4 mt-6">Explorar catálogo</a></div>
    @else
        <div class="grid lg:grid-cols-[1fr_330px] gap-7 items-start mt-8">
            <form method="post" action="{{ route('cart.update') }}" class="bg-white border border-gray-100">
                @csrf
                @method('PATCH')
                <div class="p-5 border-b border-gray-100"><h2 class="font-black text-gpm-navy">Artigos selecionados ({{ $items->count() }})</h2></div>
                <div class="divide-y divide-gray-100">
                    @foreach($items as $line)
                        @php($product = $line['product'])
                        <div class="p-5 grid grid-cols-[76px_1fr] sm:grid-cols-[92px_1fr_140px] gap-4 items-start">
                            <a href="{{ route('marketplace.products.show', $product) }}" class="h-20 bg-gray-50 flex items-center justify-center p-2">
                                @if($product->main_image)<img src="{{ route('media.products.show', ['filename' => basename($product->main_image)]) }}" alt="" class="max-h-full max-w-full object-contain">@else<i class="fa-solid fa-box text-gray-300 text-2xl"></i>@endif
                            </a>
                            <div><a href="{{ route('marketplace.products.show', $product) }}" class="font-bold text-sm text-gpm-navy hover:text-gpm-orange">{{ $product->name }}</a><p class="text-xs text-gray-400 mt-1">{{ $product->sku ?: 'Sem referência' }} · {{ $product->unit }}</p><label class="block text-[10px] font-bold uppercase text-gray-400 mt-3">Observação do artigo<input name="items[{{ $product->id }}][notes]" value="{{ $line['notes'] }}" maxlength="1000" placeholder="Marca, modelo, especificação..." class="block w-full border-gray-200 text-xs mt-1"></label></div>
                            <div class="sm:text-right"><label class="text-[10px] font-bold uppercase text-gray-400">Quantidade<input type="number" name="items[{{ $product->id }}][quantity]" value="{{ number_format($line['quantity'],3,'.','') }}" min="0.001" step="0.001" required class="w-full sm:w-28 border-gray-200 text-sm mt-1"></label><p class="text-[10px] text-gray-400 mt-2">Preço a confirmar</p><button form="remove-{{ $product->id }}" class="text-[10px] text-red-600 font-bold uppercase mt-3" type="submit">Remover</button></div>
                        </div>
                    @endforeach
                </div>
                <div class="p-5 border-t border-gray-100 flex justify-end"><button type="submit" class="border border-gpm-navy text-gpm-navy text-xs font-bold uppercase px-6 py-3">Atualizar pedido</button></div>
            </form>
            @foreach($items as $line)
                <form id="remove-{{ $line['product']->id }}" method="post" action="{{ route('cart.items.destroy', $line['product']->id) }}" class="hidden">@csrf @method('delete')</form>
            @endforeach
            <aside class="bg-white border border-gray-100 p-6">
                <h2 class="font-black text-gpm-navy">Enviar pedido</h2><p class="text-xs text-gray-500 mt-2">Inclua uma nota geral para contextualizar a sua necessidade.</p>
                <form method="post" action="{{ route('cart.submit') }}" class="mt-5">@csrf<label class="text-xs font-bold text-gpm-navy">Observações gerais<textarea name="notes" rows="5" maxlength="5000" placeholder="Prazo pretendido, local de entrega, requisitos gerais..." class="w-full border-gray-200 text-sm mt-2"></textarea></label><button class="w-full bg-gpm-orange hover:bg-orange-700 text-white font-bold text-xs uppercase tracking-wider py-4 mt-5">Enviar pedido à GPM</button></form>
                <p class="text-[11px] text-gray-400 mt-5 leading-relaxed">O pedido será registado para análise. A equipa poderá consultar fornecedores e preparar uma proposta. Só a confirmação explícita por um funcionário autorizado transforma a proposta aceite numa encomenda confirmada.</p>
            </aside>
        </div>
    @endif
</main>
@endsection
