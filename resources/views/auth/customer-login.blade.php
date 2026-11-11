@extends('layouts.app')

@section('title', 'Entrar na conta')

@section('content')
<div class="min-h-screen bg-gpm-soft flex flex-col">
    <header class="bg-white border-b border-gray-100"><div class="max-w-site mx-auto px-6 h-20 flex items-center justify-between"><a href="{{ route('home') }}" class="font-black text-2xl text-gpm-navy">GPM<span class="text-gpm-orange">.</span></a><a href="{{ route('marketplace.index') }}" class="text-xs font-bold uppercase text-gpm-navy">Marketplace</a></div></header>
    <main class="flex-1 flex items-center justify-center px-5 py-12">
        <form method="post" action="{{ route('customer.login.store') }}" class="w-full max-w-md bg-white border border-gray-100 shadow-sm p-7 md:p-9">
            @csrf
            <span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Área de cliente</span>
            <h1 class="text-3xl font-black text-gpm-navy mt-3">Bem-vindo de volta.</h1>
            <p class="text-sm text-gray-500 mt-2">Entre para acompanhar os seus pedidos e propostas.</p>
            @if($errors->any())<div class="bg-red-50 text-red-700 text-sm p-3 mt-5">{{ $errors->first() }}</div>@endif
            <label class="block text-xs font-bold text-gpm-navy mt-6">Email<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="mt-2 w-full border-gray-200 text-sm"></label>
            <label class="block text-xs font-bold text-gpm-navy mt-4">Palavra-passe<input type="password" name="password" required autocomplete="current-password" class="mt-2 w-full border-gray-200 text-sm"></label>
            <button class="w-full bg-gpm-orange hover:bg-orange-700 text-white font-bold text-xs uppercase py-4 mt-6 transition" type="submit">Entrar na minha conta</button>
            <p class="text-sm text-gray-500 mt-6 text-center">Ainda não tem conta? <a href="{{ route('customer.register') }}" class="text-gpm-orange font-bold">Criar conta</a></p>
        </form>
    </main>
</div>
@endsection
