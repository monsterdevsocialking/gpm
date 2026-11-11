@extends('layouts.app')

@section('title', 'Soluções em Suprimentos e Procurement')
@section('description', 'A GPM Conecta apoia empresas em Angola com procurement, fornecimento de materiais, equipamentos e produtos profissionais.')

@section('content')
@php
    $companyName = $company?->company_name ?: 'GPM Conecta';
    $phone = $company?->phone ?: '';
    $email = $company?->email ?: '';
@endphp
<div class="bg-gpm-navy text-white text-[11px] py-2">
    <div class="max-w-site mx-auto px-6 flex flex-wrap justify-between gap-2 items-center">
        <div class="flex gap-5">
            @if($phone)<a href="tel:{{ $phone }}"><i class="fa-solid fa-phone text-gpm-orange mr-2"></i>{{ $phone }}</a>@endif
            @if($email)<a class="hidden sm:inline" href="mailto:{{ $email }}"><i class="fa-solid fa-envelope text-gpm-orange mr-2"></i>{{ $email }}</a>@endif
        </div>
        <span class="hidden sm:inline text-white/65 tracking-wider">SUPRIMENTOS · PROCUREMENT · ANGOLA</span>
    </div>
</div>
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-gray-100">
    <div class="max-w-site mx-auto px-6 h-[76px] flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2" aria-label="GPM Conecta — Início">
            <span class="w-9 h-9 bg-gpm-orange text-white grid place-items-center"><i class="fa-solid fa-layer-group"></i></span>
            <span class="text-2xl font-black text-gpm-navy tracking-tighter">GPM<span class="text-gpm-orange">.</span></span>
        </a>
        <nav class="hidden lg:flex items-center gap-8 text-[11px] font-extrabold uppercase tracking-wider text-gpm-navy">
            <a href="#inicio" class="text-gpm-orange">Início</a><a href="#servicos" class="hover:text-gpm-orange">Serviços</a>
            <a href="#solucoes" class="hover:text-gpm-orange">Soluções</a><a href="#processo" class="hover:text-gpm-orange">Como trabalhamos</a>
            <a href="#contactos" class="hover:text-gpm-orange">Contactos</a>
            <a href="{{ route('marketplace.index') }}" class="text-gpm-orange">Marketplace <i class="fa-solid fa-arrow-up-right-from-square ml-1"></i></a>
            @auth('customer')<a href="{{ route('customer.dashboard') }}" class="hover:text-gpm-orange">A minha conta</a>@else<a href="{{ route('customer.login') }}" class="hover:text-gpm-orange">Entrar</a>@endauth
        </nav>
        <div class="flex items-center gap-4">
            <a href="{{ route('marketplace.index') }}" class="text-gpm-navy hover:text-gpm-orange text-sm font-bold"><i class="fa-solid fa-store mr-2"></i><span class="hidden sm:inline">Marketplace</span></a>
            <button id="gpm-menu-button" class="lg:hidden text-gpm-navy text-xl" aria-label="Abrir menu" aria-controls="gpm-mobile-menu" aria-expanded="false"><i class="fa-solid fa-bars"></i></button>
        </div>
    </div>
    <nav id="gpm-mobile-menu" class="hidden flex-col gap-5 px-6 py-5 border-t border-gray-100 text-xs font-bold uppercase text-gpm-navy lg:hidden">
        <a href="#inicio">Início</a><a href="#servicos">Serviços</a><a href="#solucoes">Soluções</a><a href="#processo">Como trabalhamos</a><a href="#contactos">Contactos</a>
        <a class="text-gpm-orange" href="{{ route('marketplace.index') }}">Ir ao Marketplace</a>
        @auth('customer')<a href="{{ route('customer.dashboard') }}">A minha conta</a>@else<a href="{{ route('customer.login') }}">Entrar na conta</a>@endauth
    </nav>
</header>

<main>
    <section id="inicio" class="relative overflow-hidden bg-white">
        <div class="absolute inset-y-0 right-0 w-[58%] bg-gray-50 hidden md:block" style="clip-path:polygon(20% 0,100% 0,100% 100%,0 100%)"></div>
        <div class="absolute right-0 top-0 w-[52%] h-full hidden lg:block">
            <img src="https://images.unsplash.com/photo-1581092160562-40aa08e78837?q=80&w=1400&auto=format&fit=crop" alt="Equipamentos e operações industriais" class="w-full h-full object-cover" style="clip-path:polygon(25% 0,100% 0,100% 100%,0 100%)">
        </div>
        <div class="max-w-site mx-auto px-6 py-24 md:py-32 relative z-10">
            <div class="max-w-2xl reveal">
                <span class="inline-flex items-center gap-3 text-[10px] md:text-xs font-extrabold tracking-[.22em] text-gpm-orange uppercase"><span class="w-8 h-[2px] bg-gpm-orange"></span> Suprimentos · Procurement · Fornecimento B2B</span>
                <h1 class="text-[clamp(2.7rem,7vw,5.5rem)] font-black text-gpm-navy leading-[1.02] uppercase tracking-tight mt-7">Fornecemos<br>soluções para<br><span class="text-gpm-orange">o seu negócio.</span></h1>
                <p class="text-gray-600 text-sm md:text-base max-w-lg leading-relaxed mt-7">Ajudamos empresas a encontrar, comprar e receber o que precisam para trabalhar: materiais, equipamentos, produtos industriais e soluções profissionais, nacionais ou importadas.</p>
                <div class="flex flex-wrap gap-3 mt-8">
                    <a href="{{ route('marketplace.index') }}" class="bg-gpm-orange hover:bg-orange-700 text-white font-bold text-xs uppercase tracking-wider px-7 py-4 transition shadow-lg"><i class="fa-solid fa-store mr-2"></i>Entrar no Marketplace</a>
                    <a href="#contactos" class="border border-gpm-navy text-gpm-navy hover:bg-gpm-navy hover:text-white font-bold text-xs uppercase tracking-wider px-7 py-4 transition">Falar connosco</a>
                </div>
            </div>
        </div>
    </section>

    <section id="servicos" class="py-20 md:py-24 relative overflow-hidden">
        <span class="watermark">SERVIÇOS</span>
        <div class="max-w-site mx-auto px-6 relative z-10">
            <h2 class="text-4xl md:text-5xl font-black text-gpm-navy uppercase tracking-tight mb-12 reveal">Serviços</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach([
                    ['Procurement e Gestão','Gerenciamos o processo de cotação e negociação para a sua empresa.'],
                    ['Fornecimento B2B','Fornecimento de equipamentos, materiais, insumos e artigos profissionais.'],
                    ['Representação','Representação comercial de equipamentos nacionais e importados.'],
                    ['Importação','Importação de insumos industriais e materiais especializados.']
                ] as $service)
                    <article class="p-7 border border-gray-100 border-b-4 border-b-transparent hover:border-b-gpm-orange hover:-translate-y-1 transition bg-white reveal">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">GPM CONECTA</span>
                        <h3 class="text-lg font-bold text-gpm-navy mt-3 mb-3">{{ $service[0] }}</h3>
                        <p class="text-xs text-gray-500 leading-relaxed">{{ $service[1] }}</p>
                        <a href="{{ route('marketplace.index') }}" class="text-gpm-orange font-bold text-2xl inline-block mt-5" aria-label="Explorar marketplace">+</a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-20 bg-gpm-soft">
        <div class="max-w-site mx-auto px-6">
            <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4 border-b border-gray-200 pb-5 mb-8 reveal">
                <div><span class="text-[10px] font-extrabold tracking-[.2em] text-gpm-orange uppercase">Catálogo GPM</span><h2 class="text-3xl md:text-4xl font-black text-gpm-navy uppercase mt-2">Produtos em destaque</h2><p class="text-gray-500 text-sm mt-2">Alguns dos artigos disponíveis no marketplace.</p></div>
                <a href="{{ route('marketplace.index') }}" class="bg-gpm-orange hover:bg-orange-700 text-white font-bold text-xs uppercase px-6 py-3 transition whitespace-nowrap">Ver todos os produtos <i class="fa-solid fa-arrow-right ml-2"></i></a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @forelse($products as $product)
                    <article class="product-card bg-white flex flex-col reveal">
                        <a href="{{ route('marketplace.products.show', $product) }}" class="h-52 bg-white border-b border-gray-100 p-5 flex items-center justify-center overflow-hidden">
                            @if($product->main_image)
                                <img src="{{ route('media.products.show', ['filename' => basename($product->main_image)]) }}" alt="{{ $product->name }}" class="max-h-full max-w-full object-contain" loading="lazy">
                            @else
                                <i class="fa-solid fa-box-open text-gray-300 text-5xl"></i>
                            @endif
                        </a>
                        <div class="p-5 flex-1"><h3 class="font-bold text-sm text-gpm-navy">{{ $product->name }}</h3><p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 70) }}</p><div class="text-lg font-black text-gpm-navy mt-4">{{ number_format((float) $product->price, 2, ',', '.') }} Kz</div></div>
                        <div class="px-5 pb-5"><a href="{{ route('marketplace.products.show', $product) }}" class="block text-center bg-gpm-navy hover:bg-slate-800 text-white text-xs font-bold uppercase py-3 transition">Ver detalhes</a></div>
                    </article>
                @empty
                    <div class="col-span-full bg-white border border-gray-100 text-center py-12 text-gray-500"><i class="fa-solid fa-box-open text-4xl text-gray-300 mb-3"></i><p>Nenhum produto disponível neste momento.</p></div>
                @endforelse
            </div>
        </div>
    </section>

    <section id="solucoes" class="py-20 md:py-24 relative overflow-hidden">
        <span class="watermark">SOLUÇÕES</span>
        <div class="max-w-site mx-auto px-6 relative z-10">
            <h2 class="text-4xl font-black text-gpm-navy uppercase mb-10 reveal">Soluções e setores</h2>
            <div class="grid md:grid-cols-3 gap-5">
                <div class="md:col-span-2 h-80 relative group overflow-hidden reveal"><img src="https://images.unsplash.com/photo-1581092334651-ddf26d9a09d0?q=80&w=1200&auto=format&fit=crop" alt="Equipamentos especializados" class="w-full h-full object-cover group-hover:scale-105 transition duration-500"><div class="absolute inset-0 bg-gradient-to-t from-gpm-navy/90 to-transparent flex flex-col justify-end p-8"><h3 class="text-2xl font-bold text-white">Equipamentos especializados</h3><p class="text-gray-200 text-sm mt-2">Soluções para operações profissionais e industriais.</p></div></div>
                <div class="border border-gray-100 p-8 flex flex-col justify-center items-center text-center reveal"><i class="fa-solid fa-fire-burner text-gpm-orange text-4xl mb-4"></i><span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Indústria e restauração</span><h3 class="font-bold text-gpm-navy text-lg mt-2">Fogões e equipamentos</h3><p class="text-xs text-gray-500 mt-3">Soluções para cozinhas industriais e restauração.</p><a href="{{ route('marketplace.index') }}" class="text-xs font-bold text-gpm-orange border-b border-gpm-orange mt-5">Ver produtos</a></div>
                <div class="h-56 overflow-hidden reveal"><img src="https://images.unsplash.com/photo-1503387762-592deb58ef4e?q=80&w=700&auto=format&fit=crop" alt="Construção e infraestrutura" class="w-full h-full object-cover"></div>
                <div class="border border-gray-100 p-7 flex flex-col justify-center text-center reveal"><i class="fa-solid fa-boxes-stacked text-gpm-orange text-3xl"></i><h3 class="font-bold text-gpm-navy mt-3">Materiais e insumos</h3><p class="text-xs text-gray-500 mt-2">Artigos para as necessidades diárias da sua empresa.</p></div>
                <div class="h-56 overflow-hidden reveal"><img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?q=80&w=900&auto=format&fit=crop" alt="Logística e fornecimento" class="w-full h-full object-cover"></div>
            </div>
        </div>
    </section>

    <section id="processo" class="py-20 bg-gpm-navy text-white">
        <div class="max-w-site mx-auto px-6">
            <div class="max-w-2xl mb-12 reveal"><span class="text-[10px] font-extrabold tracking-[.2em] text-gpm-orange uppercase">Um processo simples</span><h2 class="text-3xl md:text-4xl font-black mt-3">Da necessidade à entrega.</h2><p class="text-white/65 text-sm mt-4 leading-relaxed">Cada pedido começa por perceber exactamente o que a sua empresa procura. Depois tratamos da pesquisa, da cotação e do fornecimento.</p></div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 border-t border-white/15 border-b">
                @foreach([['01','Pedido','Recebemos a lista, as quantidades e os detalhes do que procura.'],['02','Pesquisa','Procuramos opções e fornecedores adequados ao pedido.'],['03','Cotação','Apresentamos as condições para análise e aprovação.'],['04','Fornecimento','Acompanhamos o fornecimento até à entrega acordada.']] as $step)
                    <div class="py-7 pr-5 border-b lg:border-b-0 lg:border-r border-white/15 reveal"><span class="text-3xl font-black text-gpm-orange">{{ $step[0] }}</span><h3 class="font-bold mt-4">{{ $step[1] }}</h3><p class="text-sm text-white/60 mt-2 leading-relaxed">{{ $step[2] }}</p></div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-20 bg-gpm-orange text-white">
        <div class="max-w-site mx-auto px-6 grid md:grid-cols-2 gap-10 items-center">
            <img src="https://images.unsplash.com/photo-1494412519320-aa613dfb7738?q=80&w=1000&auto=format&fit=crop" alt="Logística e gestão de fornecimento" class="w-full shadow-2xl reveal">
            <div class="reveal"><span class="text-xs font-bold tracking-widest uppercase text-white/75">Atendimento a empresas</span><h2 class="text-4xl md:text-5xl font-black mt-4 leading-tight">Tem uma lista de compras?</h2><p class="text-white/90 text-sm leading-relaxed mt-5">Envie-nos os produtos, as quantidades e, se possível, as especificações. A equipa poderá analisar o pedido e preparar uma proposta de fornecimento.</p><a href="{{ route('marketplace.index') }}" class="inline-block bg-white text-gpm-orange font-bold text-xs uppercase px-7 py-4 mt-7 hover:bg-gray-100 transition">Aceder ao marketplace</a></div>
        </div>
    </section>

    <section id="contactos" class="py-20 bg-white">
        <div class="max-w-site mx-auto px-6 grid lg:grid-cols-2 gap-12">
            <div class="reveal"><span class="text-[10px] font-extrabold tracking-[.2em] text-gpm-orange uppercase">Fale com a GPM</span><h2 class="text-4xl font-black text-gpm-navy mt-3">Vamos encontrar a solução certa.</h2><p class="text-gray-500 text-sm mt-5 leading-relaxed">Partilhe a sua necessidade. A nossa equipa entrará em contacto para compreender o pedido e orientar os próximos passos.</p>
                @if($phone)<p class="mt-7 text-sm text-gpm-navy"><i class="fa-solid fa-phone text-gpm-orange mr-3"></i>{{ $phone }}</p>@endif
                @if($email)<p class="mt-3 text-sm text-gpm-navy"><i class="fa-solid fa-envelope text-gpm-orange mr-3"></i>{{ $email }}</p>@endif
            </div>
            <form action="{{ route('contact.store') }}" method="post" class="border border-gray-100 shadow-sm p-7 md:p-9 reveal">
                @csrf
                @if(session('contact_success'))<div class="bg-green-50 text-green-800 p-3 text-sm mb-5">{{ session('contact_success') }}</div>@endif
                @if($errors->any())<div class="bg-red-50 text-red-700 p-3 text-sm mb-5">Verifique os campos e tente novamente.</div>@endif
                <div class="grid sm:grid-cols-2 gap-4">
                    <label class="text-xs font-bold text-gpm-navy">Nome *<input name="name" value="{{ old('name') }}" required maxlength="150" class="mt-2 w-full border-gray-200 text-sm focus:border-gpm-orange"></label>
                    <label class="text-xs font-bold text-gpm-navy">Email *<input type="email" name="email" value="{{ old('email') }}" required maxlength="190" class="mt-2 w-full border-gray-200 text-sm focus:border-gpm-orange"></label>
                    <label class="text-xs font-bold text-gpm-navy">Telefone<input name="phone" value="{{ old('phone') }}" maxlength="40" class="mt-2 w-full border-gray-200 text-sm focus:border-gpm-orange"></label>
                    <label class="text-xs font-bold text-gpm-navy">Assunto<input name="subject" value="{{ old('subject') }}" maxlength="180" class="mt-2 w-full border-gray-200 text-sm focus:border-gpm-orange"></label>
                </div>
                <label class="block text-xs font-bold text-gpm-navy mt-4">Mensagem *<textarea name="message" rows="5" required maxlength="5000" class="mt-2 w-full border-gray-200 text-sm focus:border-gpm-orange">{{ old('message') }}</textarea></label>
                <button class="mt-5 bg-gpm-navy hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider px-8 py-4 transition" type="submit">Enviar mensagem <i class="fa-solid fa-arrow-right ml-2"></i></button>
            </form>
        </div>
    </section>
</main>
<footer class="bg-[#081a30] text-white py-8">
    <div class="max-w-site mx-auto px-6 flex flex-col sm:flex-row justify-between gap-4 items-center"><a href="{{ route('home') }}" class="font-black text-2xl tracking-tighter">GPM<span class="text-gpm-orange">.</span></a><p class="text-white/50 text-xs">© {{ date('Y') }} {{ $companyName }}. Todos os direitos reservados.</p><a href="{{ route('marketplace.index') }}" class="text-xs font-bold uppercase text-gpm-orange">Marketplace <i class="fa-solid fa-arrow-right ml-1"></i></a></div>
</footer>
@endsection
