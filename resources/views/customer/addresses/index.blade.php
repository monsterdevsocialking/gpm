@extends('layouts.app')

@section('title', 'Moradas de entrega')

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
<main class="max-w-5xl mx-auto px-5 py-10">
    <a href="{{ route('customer.dashboard') }}" class="text-xs font-bold uppercase text-gpm-orange"><i class="fa-solid fa-arrow-left mr-2"></i>Voltar à minha conta</a>
    <div class="mt-5 mb-7">
        <span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Portal empresarial</span>
        <h1 class="text-3xl font-black text-gpm-navy mt-2">Moradas de entrega</h1>
        <p class="text-sm text-gray-500 mt-2">Guarde diferentes locais de entrega e escolha a morada principal da sua empresa.</p>
    </div>

    @if(session('success'))<div class="bg-green-50 text-green-800 text-sm p-4 mb-5">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-50 text-red-700 text-sm p-4 mb-5">{{ $errors->first() }}</div>@endif

    <section class="bg-white border border-gray-100 p-6 md:p-8 mb-7">
        <h2 class="font-black text-gpm-navy text-lg mb-5">Adicionar nova morada</h2>
        <form method="post" action="{{ route('customer.addresses.store') }}">
            @csrf
            <div class="grid sm:grid-cols-2 gap-5">
                <label class="block text-xs font-bold text-gpm-navy">Identificação (ex.: Armazém, Escritório)
                    <input name="label" maxlength="100" value="{{ old('label') }}" class="w-full border-gray-200 text-sm mt-2">
                </label>
                <label class="block text-xs font-bold text-gpm-navy">Província
                    <input name="province" maxlength="100" value="{{ old('province') }}" class="w-full border-gray-200 text-sm mt-2">
                </label>
                <label class="block text-xs font-bold text-gpm-navy sm:col-span-2">Morada completa
                    <textarea name="address" rows="2" maxlength="2000" class="w-full border-gray-200 text-sm mt-2">{{ old('address') }}</textarea>
                </label>
                <label class="block text-xs font-bold text-gpm-navy">Rua / Avenida
                    <input name="street" maxlength="180" value="{{ old('street') }}" class="w-full border-gray-200 text-sm mt-2">
                </label>
                <label class="block text-xs font-bold text-gpm-navy">Bairro
                    <input name="neighborhood" maxlength="120" value="{{ old('neighborhood') }}" class="w-full border-gray-200 text-sm mt-2">
                </label>
                <label class="block text-xs font-bold text-gpm-navy">Cidade / Município
                    <input name="city" maxlength="100" value="{{ old('city') }}" class="w-full border-gray-200 text-sm mt-2">
                </label>
                <label class="block text-xs font-bold text-gpm-navy">Ponto de referência
                    <input name="reference_point" maxlength="255" value="{{ old('reference_point') }}" class="w-full border-gray-200 text-sm mt-2">
                </label>
            </div>
            <p class="text-xs text-gray-400 mt-3">Preencha a morada completa ou a rua/avenida.</p>
            <button class="bg-gpm-orange text-white text-xs font-bold uppercase px-7 py-4 mt-5">Guardar morada</button>
        </form>
    </section>

    <section class="space-y-4">
        @forelse($addresses as $address)
            <article class="bg-white border {{ $address->is_default ? 'border-gpm-orange' : 'border-gray-100' }} p-5 md:p-6">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-black text-gpm-navy">{{ $address->label ?: 'Morada de entrega' }}</h2>
                            @if($address->is_default)<span class="bg-orange-50 text-gpm-orange text-[10px] font-bold uppercase px-2 py-1">Principal</span>@endif
                        </div>
                        <p class="text-sm text-gray-600 mt-2">{{ $address->address ?: $address->street }}</p>
                        @if($address->neighborhood)<p class="text-sm text-gray-500">{{ $address->neighborhood }}</p>@endif
                        <p class="text-sm text-gray-500">{{ collect([$address->city, $address->province])->filter()->implode(', ') }}</p>
                        @if($address->reference_point)<p class="text-xs text-gray-400 mt-1">Referência: {{ $address->reference_point }}</p>@endif
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        @unless($address->is_default)
                            <form method="post" action="{{ route('customer.addresses.default', $address) }}">@csrf @method('PATCH')
                                <button class="text-xs font-bold uppercase text-gpm-orange">Tornar principal</button>
                            </form>
                        @endunless
                        <form method="post" action="{{ route('customer.addresses.destroy', $address) }}" onsubmit="return confirm('Tem a certeza de que pretende remover esta morada?')">@csrf @method('DELETE')
                            <button class="text-xs font-bold uppercase text-red-600">Remover</button>
                        </form>
                    </div>
                </div>
                <details class="mt-5 border-t border-gray-100 pt-4">
                    <summary class="cursor-pointer text-xs font-bold uppercase text-gpm-navy">Editar esta morada</summary>
                    <form method="post" action="{{ route('customer.addresses.update', $address) }}" class="mt-4">
                        @csrf @method('PUT')
                        <div class="grid sm:grid-cols-2 gap-4">
                            <label class="block text-xs font-bold text-gpm-navy">Identificação
                                <input name="label" maxlength="100" value="{{ $address->label }}" class="w-full border-gray-200 text-sm mt-2">
                            </label>
                            <label class="block text-xs font-bold text-gpm-navy">Província
                                <input name="province" maxlength="100" value="{{ $address->province }}" class="w-full border-gray-200 text-sm mt-2">
                            </label>
                            <label class="block text-xs font-bold text-gpm-navy sm:col-span-2">Morada completa
                                <textarea name="address" rows="2" maxlength="2000" class="w-full border-gray-200 text-sm mt-2">{{ $address->address }}</textarea>
                            </label>
                            <label class="block text-xs font-bold text-gpm-navy">Rua / Avenida
                                <input name="street" maxlength="180" value="{{ $address->street }}" class="w-full border-gray-200 text-sm mt-2">
                            </label>
                            <label class="block text-xs font-bold text-gpm-navy">Bairro
                                <input name="neighborhood" maxlength="120" value="{{ $address->neighborhood }}" class="w-full border-gray-200 text-sm mt-2">
                            </label>
                            <label class="block text-xs font-bold text-gpm-navy">Cidade / Município
                                <input name="city" maxlength="100" value="{{ $address->city }}" class="w-full border-gray-200 text-sm mt-2">
                            </label>
                            <label class="block text-xs font-bold text-gpm-navy">Ponto de referência
                                <input name="reference_point" maxlength="255" value="{{ $address->reference_point }}" class="w-full border-gray-200 text-sm mt-2">
                            </label>
                        </div>
                        <button class="bg-gpm-navy text-white text-xs font-bold uppercase px-6 py-3 mt-4">Actualizar morada</button>
                    </form>
                </details>
            </article>
        @empty
            <div class="bg-white border border-gray-100 p-8 text-center">
                <i class="fa-solid fa-location-dot text-gray-300 text-3xl"></i>
                <p class="text-sm text-gray-500 mt-3">Ainda não guardou moradas de entrega.</p>
            </div>
        @endforelse
    </section>
</main>
@endsection
