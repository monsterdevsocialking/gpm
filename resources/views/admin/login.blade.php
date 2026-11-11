@extends('layouts.admin')

@section('title', 'Entrar no painel')

@section('content')
<main class="min-h-screen flex items-center justify-center px-5 py-12 bg-gpm-navy">
    <form method="post" action="{{ route('admin.login.store') }}" class="w-full max-w-md bg-white p-8 md:p-10 shadow-2xl">
        @csrf
        <a href="{{ route('home') }}" class="font-black text-3xl text-gpm-navy tracking-tighter">GPM<span class="text-gpm-orange">.</span></a>
        <span class="block text-[10px] uppercase tracking-[.2em] font-extrabold text-gpm-orange mt-8">Acesso interno</span>
        <h1 class="text-2xl font-black text-gpm-navy mt-2">Painel administrativo</h1>
        <p class="text-sm text-gray-500 mt-2">Entre com as credenciais da sua conta GPM.</p>
        @if($errors->any())<div class="bg-red-50 text-red-700 text-sm p-3 mt-5">{{ $errors->first() }}</div>@endif
        <label class="block text-xs font-bold text-gpm-navy mt-7">Email<input type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="mt-2 w-full border-gray-200 text-sm"></label>
        <label class="block text-xs font-bold text-gpm-navy mt-4">Palavra-passe<input type="password" name="password" required autocomplete="current-password" class="mt-2 w-full border-gray-200 text-sm"></label>
        <button type="submit" class="w-full bg-gpm-orange hover:bg-orange-700 text-white font-bold text-xs uppercase tracking-wider py-4 mt-7">Entrar no painel</button>
        <a href="{{ route('home') }}" class="block text-center text-xs text-gray-400 mt-6 hover:text-gpm-orange">Voltar ao site público</a>
    </form>
</main>
@endsection
