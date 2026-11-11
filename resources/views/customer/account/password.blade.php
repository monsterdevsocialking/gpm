@extends('layouts.app')

@section('title', 'Segurança da conta')

@section('content')
<header class="bg-white border-b border-gray-100">
    <div class="max-w-site mx-auto px-6 h-20 flex items-center justify-between">
        <a href="{{ route('home') }}" class="font-black text-2xl text-gpm-navy">GPM<span class="text-gpm-orange">.</span></a>
        <nav class="flex items-center gap-4 text-xs font-bold uppercase">
            <a href="{{ route('customer.dashboard') }}" class="text-gray-500 hover:text-gpm-orange">A minha conta</a>
            <a href="{{ route('customer.profile.edit') }}" class="text-gray-500 hover:text-gpm-orange">Os meus dados</a>
        </nav>
    </div>
</header>
<main class="max-w-2xl mx-auto px-5 py-10">
    <a href="{{ route('customer.dashboard') }}" class="text-xs font-bold uppercase text-gpm-orange"><i class="fa-solid fa-arrow-left mr-2"></i>Voltar à minha conta</a>
    <div class="mt-5 mb-7">
        <span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Segurança</span>
        <h1 class="text-3xl font-black text-gpm-navy mt-2">Alterar palavra-passe</h1>
        <p class="text-sm text-gray-500 mt-2">Confirme a palavra-passe actual antes de definir uma nova.</p>
    </div>
    @if(session('success'))<div class="bg-green-50 text-green-800 text-sm p-4 mb-5">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 text-red-700 text-sm p-4 mb-5">{{ $errors->first() }}</div>@endif
    <section class="bg-white border border-gray-100 p-6 md:p-8">
        <form method="post" action="{{ route('customer.password.update') }}">
            @csrf
            @method('PUT')
            <label class="block text-xs font-bold text-gpm-navy">Palavra-passe actual
                <input type="password" name="current_password" required autocomplete="current-password" class="w-full border-gray-200 text-sm mt-2">
            </label>
            <label class="block text-xs font-bold text-gpm-navy mt-5">Nova palavra-passe
                <input type="password" name="password" required minlength="8" autocomplete="new-password" class="w-full border-gray-200 text-sm mt-2">
            </label>
            <label class="block text-xs font-bold text-gpm-navy mt-5">Confirmar nova palavra-passe
                <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="w-full border-gray-200 text-sm mt-2">
            </label>
            <button class="bg-gpm-orange text-white text-xs font-bold uppercase px-7 py-4 mt-7">Actualizar palavra-passe</button>
        </form>
    </section>
</main>
@endsection
