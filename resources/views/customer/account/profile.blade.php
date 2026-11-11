@extends('layouts.app')

@section('title', 'Os meus dados')

@section('content')
<header class="bg-white border-b border-gray-100">
    <div class="max-w-site mx-auto px-6 h-20 flex items-center justify-between">
        <a href="{{ route('home') }}" class="font-black text-2xl text-gpm-navy">GPM<span class="text-gpm-orange">.</span></a>
        <nav class="flex items-center gap-4 text-xs font-bold uppercase">
            <a href="{{ route('customer.dashboard') }}" class="text-gray-500 hover:text-gpm-orange">A minha conta</a>
            <a href="{{ route('customer.addresses.index') }}" class="text-gray-500 hover:text-gpm-orange">Moradas de entrega</a>
            <a href="{{ route('customer.password.edit') }}" class="text-gray-500 hover:text-gpm-orange">Segurança</a>
        </nav>
    </div>
</header>
<main class="max-w-3xl mx-auto px-5 py-10">
    <a href="{{ route('customer.dashboard') }}" class="text-xs font-bold uppercase text-gpm-orange"><i class="fa-solid fa-arrow-left mr-2"></i>Voltar à minha conta</a>
    <div class="mt-5 mb-7">
        <span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Portal empresarial</span>
        <h1 class="text-3xl font-black text-gpm-navy mt-2">Os meus dados</h1>
        <p class="text-sm text-gray-500 mt-2">Mantenha os dados da sua conta e os contactos de entrega actualizados.</p>
    </div>
    @if(session('success'))<div class="bg-green-50 text-green-800 text-sm p-4 mb-5">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 text-red-700 text-sm p-4 mb-5">{{ $errors->first() }}</div>@endif
    <section class="bg-white border border-gray-100 p-6 md:p-8">
        <form method="post" action="{{ route('customer.profile.update') }}">
            @csrf
            @method('PUT')
            <div class="grid sm:grid-cols-2 gap-5">
                <label class="block text-xs font-bold text-gpm-navy sm:col-span-2">Nome / Empresa *
                    <input name="name" required maxlength="180" value="{{ old('name',$customer->name) }}" class="w-full border-gray-200 text-sm mt-2">
                </label>
                <label class="block text-xs font-bold text-gpm-navy">Email *
                    <input type="email" name="email" required maxlength="190" value="{{ old('email',$customer->email) }}" class="w-full border-gray-200 text-sm mt-2">
                </label>
                <label class="block text-xs font-bold text-gpm-navy">Telefone *
                    <input name="phone" required maxlength="40" value="{{ old('phone',$customer->phone) }}" class="w-full border-gray-200 text-sm mt-2">
                </label>
                <label class="block text-xs font-bold text-gpm-navy">NIF
                    <input name="nif" maxlength="50" value="{{ old('nif',$customer->nif) }}" class="w-full border-gray-200 text-sm mt-2">
                </label>
                <label class="block text-xs font-bold text-gpm-navy">Província
                    <input name="province" maxlength="100" value="{{ old('province',$customer->province) }}" class="w-full border-gray-200 text-sm mt-2">
                </label>
                <label class="block text-xs font-bold text-gpm-navy sm:col-span-2">Cidade / Município
                    <input name="city" maxlength="100" value="{{ old('city',$customer->city) }}" class="w-full border-gray-200 text-sm mt-2">
                </label>
                <label class="block text-xs font-bold text-gpm-navy sm:col-span-2">Morada habitual
                    <textarea name="address" rows="3" maxlength="2000" class="w-full border-gray-200 text-sm mt-2">{{ old('address',$customer->address) }}</textarea>
                </label>
            </div>
            <div class="flex flex-wrap items-center gap-4 mt-7">
                <button class="bg-gpm-orange text-white text-xs font-bold uppercase px-7 py-4">Guardar alterações</button>
                <a href="{{ route('customer.addresses.index') }}" class="text-xs font-bold uppercase text-gpm-navy">Gerir moradas de entrega</a>
                <a href="{{ route('customer.password.edit') }}" class="text-xs font-bold uppercase text-gpm-navy">Alterar palavra-passe</a>
            </div>
        </form>
    </section>
</main>
@endsection
