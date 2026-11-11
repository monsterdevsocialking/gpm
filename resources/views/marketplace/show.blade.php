@extends('layouts.app')

@section('title', $product->name)
@section('description', \Illuminate\Support\Str::limit(strip_tags((string)$product->description), 155))

@section('content')
<header class="bg-white border-b border-gray-100">
    <div class="max-w-site mx-auto px-6 h-[76px] flex items-center justify-between"><a href="{{ route('home') }}" class="font-black text-2xl tracking-tighter text-gpm-navy">GPM<span class="text-gpm-orange">.</span></a><a href="{{ route('marketplace.index') }}" class="text-xs font-bold uppercase text-gpm-navy hover:text-gpm-orange"><i class="fa-solid fa-arrow-left mr-2"></i>Voltar ao marketplace</a></div>
</header>
<main class="max-w-site mx-auto px-6 py-10 md:py-16">
    <nav class="text-xs text-gray-400 mb-8"><a href="{{ route('home') }}">Início</a><span class="mx-2">/</span><a href="{{ route('marketplace.index') }}">Marketplace</a><span class="mx-2">/</span><span class="text-gpm-navy">{{ $product->name }}</span></nav>
    <div class="grid md:grid-cols-2 gap-10 lg:gap-16 items-start">
        <div>
            <div class="bg-white border border-gray-100 min-h-[320px] md:min-h-[480px] p-8 flex items-center justify-center">
                @if($product->main_image)
                    <img src="{{ route('media.products.show', ['filename' => basename($product->main_image)]) }}" alt="{{ $product->name }}" class="max-w-full max-h-[430px] object-contain">
                @elseif($product->images->isNotEmpty())
                    <img src="{{ route('media.products.show', ['filename' => basename($product->images->first()->image_path)]) }}" alt="{{ $product->name }}" class="max-w-full max-h-[430px] object-contain">
                @else
                    <i class="fa-solid fa-box-open text-gray-200 text-7xl"></i>
                @endif
            </div>
            @if($product->images->isNotEmpty())
                <div class="flex gap-3 mt-3 flex-wrap">
                    @foreach($product->images as $image)
                        <img src="{{ route('media.products.show', ['filename' => basename($image->image_path)]) }}" alt="{{ $image->alt_text ?: $product->name }}" loading="lazy" class="w-20 h-20 bg-white border border-gray-100 p-2 object-contain">
                    @endforeach
                </div>
            @endif
        </div>
        <section>
            <span class="text-[10px] font-extrabold uppercase tracking-[.2em] text-gpm-orange">{{ $product->category?->name ?: 'GPM Conecta' }}</span>
            <h1 class="text-3xl md:text-4xl font-black text-gpm-navy mt-3">{{ $product->name }}</h1>
            @if($product->sku)<p class="text-xs text-gray-400 mt-3">Referência: {{ $product->sku }}</p>@endif
            <div class="text-2xl font-black text-gpm-navy mt-7">{{ number_format((float)$product->price, 2, ',', '.') }} {{ $currency }}</div>
            <p class="text-xs text-gray-500 mt-2">Preço indicativo. Confirme disponibilidade, condições e prazo com a equipa GPM.</p>
            <div class="border-t border-b border-gray-100 py-5 my-7"><h2 class="text-xs font-black uppercase text-gpm-navy mb-3">Descrição</h2><div class="text-sm text-gray-600 leading-7 whitespace-pre-line">{{ $product->description ?: 'Consulte a equipa GPM para obter mais informações sobre este produto.' }}</div></div>
            <form method="post" action="{{ route('cart.items.store', $product) }}" class="bg-gpm-soft border border-gray-100 p-5 mt-7">
                @csrf
                <h2 class="font-black text-sm text-gpm-navy">Adicionar ao pedido de fornecimento</h2>
                <div class="grid sm:grid-cols-[130px_1fr] gap-3 mt-4">
                    <label class="text-[10px] font-bold uppercase text-gray-500">Quantidade<input type="number" name="quantity" min="0.001" step="0.001" value="1" required class="w-full border-gray-200 text-sm mt-1"></label>
                    <label class="text-[10px] font-bold uppercase text-gray-500">Especificações / observações<input name="notes" maxlength="1000" placeholder="Marca, modelo, medidas, requisitos..." class="w-full border-gray-200 text-sm mt-1"></label>
                </div>
                <button type="submit" class="bg-gpm-orange hover:bg-orange-700 text-white font-bold text-xs uppercase tracking-wider px-7 py-4 mt-4 transition"><i class="fa-solid fa-plus mr-2"></i>Adicionar ao pedido</button>
            </form>
            <div class="flex flex-wrap gap-3 mt-4"><a href="{{ route('cart.index') }}" class="border border-gpm-navy text-gpm-navy font-bold text-xs uppercase tracking-wider px-7 py-4 hover:bg-gpm-navy hover:text-white transition">Ver pedido</a><a href="{{ route('home') }}#contactos" class="text-gpm-orange font-bold text-xs uppercase px-3 py-4">Falar com a GPM</a></div>
        </section>
    </div>
</main>
<footer class="bg-[#081a30] text-white py-7"><div class="max-w-site mx-auto px-6 flex justify-between items-center"><span class="font-black">GPM<span class="text-gpm-orange">.</span></span><a href="{{ route('home') }}#contactos" class="text-xs text-gpm-orange font-bold uppercase">Contactar</a></div></footer>
@endsection
