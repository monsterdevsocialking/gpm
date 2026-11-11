@extends('layouts.app')

@section('title', 'Marketplace')
@section('description', 'Explore o catálogo GPM Conecta e encontre materiais, equipamentos e produtos profissionais.')

@section('content')
<header class="sticky top-0 z-40 bg-white border-b border-gray-100">
    <div class="max-w-site mx-auto px-6 h-[76px] flex items-center justify-between">
        <a href="{{ route('home') }}" class="font-black text-2xl tracking-tighter text-gpm-navy">GPM<span class="text-gpm-orange">.</span></a>
        <nav class="flex items-center gap-5 text-xs font-bold uppercase text-gpm-navy"><a href="{{ route('home') }}" class="hover:text-gpm-orange">Início</a><a href="{{ route('marketplace.index') }}" class="text-gpm-orange">Marketplace</a><a href="{{ route('cart.index') }}" class="hover:text-gpm-orange"><i class="fa-solid fa-clipboard-list mr-1"></i>Pedido</a></nav>
    </div>
</header>
<section class="bg-gpm-navy text-white py-12 md:py-16">
    <div class="max-w-site mx-auto px-6">
        <span class="text-[10px] font-extrabold tracking-[.22em] uppercase text-gpm-orange">GPM Conecta · Catálogo</span>
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mt-3">
            <div><h1 class="text-3xl md:text-5xl font-black uppercase">Marketplace</h1><p class="text-white/65 text-sm mt-3">Encontre produtos e consulte a equipa GPM para o seu fornecimento.</p></div>
            <form action="{{ route('marketplace.index') }}" method="get" class="flex w-full md:max-w-md bg-white p-1">
                @if($categoryId)<input type="hidden" name="categoria" value="{{ $categoryId }}">@endif
                <input type="search" name="q" value="{{ $search }}" placeholder="Pesquisar produtos, referências..." class="w-full border-0 text-sm text-gray-800 focus:ring-0">
                <button class="bg-gpm-orange text-white px-5 text-sm font-bold" type="submit" aria-label="Pesquisar"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
        </div>
    </div>
</section>
<main class="max-w-site mx-auto px-6 py-12">
    <div class="grid lg:grid-cols-[220px_1fr] gap-8">
        <aside>
            <h2 class="text-xs font-black text-gpm-navy uppercase tracking-wider mb-4">Categorias</h2>
            <nav class="border border-gray-100 bg-white p-3 flex flex-row lg:flex-col gap-2 overflow-x-auto">
                <a href="{{ route('marketplace.index', ['q' => $search]) }}" class="shrink-0 px-3 py-2 text-sm {{ !$categoryId ? 'bg-gpm-navy text-white' : 'text-gray-600 hover:bg-gray-50' }}">Todas</a>
                @foreach($categories as $category)
                    <a href="{{ route('marketplace.index', ['categoria' => $category->id, 'q' => $search]) }}" class="shrink-0 px-3 py-2 text-sm {{ (int)$categoryId === $category->id ? 'bg-gpm-navy text-white' : 'text-gray-600 hover:bg-gray-50' }}">{{ $category->name }}</a>
                @endforeach
            </nav>
        </aside>
        <section>
            <div class="flex items-center justify-between gap-3 mb-5"><h2 class="text-xl font-black text-gpm-navy">Produtos disponíveis</h2><span class="text-xs text-gray-500">{{ $products->total() }} resultado(s)</span></div>
            <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
                @forelse($products as $product)
                    <article class="product-card bg-white flex flex-col">
                        <a href="{{ route('marketplace.products.show', $product) }}" class="h-52 p-5 flex items-center justify-center border-b border-gray-100">
                            @if($product->main_image)
                                <img src="{{ route('media.products.show', ['filename' => basename($product->main_image)]) }}" alt="{{ $product->name }}" loading="lazy" class="max-w-full max-h-full object-contain">
                            @else
                                <i class="fa-solid fa-box-open text-gray-300 text-5xl"></i>
                            @endif
                        </a>
                        <div class="p-5 flex-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gpm-orange">{{ $product->category?->name ?: 'GPM Conecta' }}</span>
                            <h3 class="font-bold text-gpm-navy mt-2">{{ $product->name }}</h3>
                            <p class="text-xs text-gray-500 mt-2 leading-relaxed">{{ \Illuminate\Support\Str::limit(strip_tags((string)$product->description), 90) }}</p>
                            <div class="mt-4 text-lg font-black text-gpm-navy">{{ number_format((float)$product->price, 2, ',', '.') }} {{ $currency }}</div>
                            <span class="inline-block mt-2 text-[10px] font-bold {{ (float)$product->stock_quantity > 0 ? 'text-emerald-700' : 'text-gray-400' }}">{{ (float)$product->stock_quantity > 0 ? 'Disponibilidade sob consulta' : 'Consultar disponibilidade' }}</span>
                        </div>
                        <div class="px-5 pb-5"><a href="{{ route('marketplace.products.show', $product) }}" class="block bg-gpm-navy text-white text-center text-xs font-bold uppercase py-3 hover:bg-slate-800 transition">Ver detalhes</a></div>
                    </article>
                @empty
                    <div class="col-span-full border border-gray-100 bg-white p-12 text-center"><i class="fa-solid fa-magnifying-glass text-gray-300 text-4xl"></i><h3 class="font-bold text-gpm-navy mt-4">Nenhum produto encontrado</h3><p class="text-sm text-gray-500 mt-2">Tente alterar a pesquisa ou escolher outra categoria.</p><a href="{{ route('marketplace.index') }}" class="inline-block text-gpm-orange font-bold text-xs uppercase mt-5">Limpar filtros</a></div>
                @endforelse
            </div>
            <div class="mt-8">{{ $products->links() }}</div>
        </section>
    </div>
</main>
<footer class="bg-[#081a30] text-white py-7"><div class="max-w-site mx-auto px-6 flex justify-between items-center gap-4"><a href="{{ route('home') }}" class="font-black text-xl">GPM<span class="text-gpm-orange">.</span></a><a href="{{ route('home') }}#contactos" class="text-xs text-gpm-orange font-bold uppercase">Contactar equipa</a></div></footer>
@endsection
