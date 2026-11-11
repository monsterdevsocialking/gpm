@extends('layouts.admin')

@section('title', 'Dados da empresa')

@section('content')
<div class="min-h-screen lg:grid lg:grid-cols-[250px_1fr]">
    <aside class="bg-gpm-navy text-white p-6"><a href="{{ route('admin.dashboard') }}" class="font-black text-2xl">GPM<span class="text-gpm-orange">.</span></a><p class="text-[10px] uppercase tracking-[.2em] text-white/45 mt-2">Painel interno</p><nav class="mt-10 space-y-2 text-sm"><a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Visão geral</a><a href="{{ route('admin.catalog.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Produtos</a><a href="{{ route('admin.categories.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Categorias</a><a href="{{ route('admin.users.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Utilizadores</a><a href="{{ route('admin.company.edit') }}" class="block px-4 py-3 bg-white/10 text-white"><i class="fa-solid fa-building text-gpm-orange mr-2"></i>Dados da empresa</a></nav><form method="post" action="{{ route('admin.logout') }}" class="mt-10">@csrf<button class="text-xs font-bold uppercase text-white/60">Terminar sessão</button></form></aside>
    <main class="p-5 md:p-8 lg:p-10">
        <div class="mb-7"><span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Configuração institucional</span><h1 class="text-3xl font-black text-gpm-navy mt-2">Dados da empresa</h1><p class="text-sm text-gray-500 mt-1">Estas informações podem ser usadas nos documentos comerciais e contactos do site.</p></div>
        @if(session('success'))<div class="bg-green-50 text-green-800 text-sm p-4 mb-6">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="bg-red-50 text-red-700 text-sm p-4 mb-6">{{ $errors->first() }}</div>@endif
        <section class="bg-white border border-gray-100 p-6 md:p-8">
            <form method="post" action="{{ route('admin.company.update') }}">@csrf @method('PUT')
                <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
                    <label class="text-xs font-bold text-gpm-navy">Nome da empresa *<input name="company_name" required maxlength="180" value="{{ old('company_name',$settings->company_name) }}" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy">NIF<input name="nif" maxlength="50" value="{{ old('nif',$settings->nif) }}" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy">Telefone<input name="phone" maxlength="40" value="{{ old('phone',$settings->phone) }}" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy">Email<input type="email" name="email" maxlength="190" value="{{ old('email',$settings->email) }}" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy">Cidade<input name="city" maxlength="100" value="{{ old('city',$settings->city) }}" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy">Província<input name="province" maxlength="100" value="{{ old('province',$settings->province) }}" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy">Moeda principal<select name="currency" required class="w-full border-gray-200 text-sm mt-2">@foreach(['AOA'=>'Kwanza (AOA)','USD'=>'Dólar (USD)','EUR'=>'Euro (EUR)'] as $code=>$label)<option value="{{ $code }}" @selected(old('currency',$settings->currency)===$code)>{{ $label }}</option>@endforeach</select></label>
                    <label class="text-xs font-bold text-gpm-navy">Taxa padrão de entrega<input type="number" name="delivery_fee" min="0" step="0.01" required value="{{ old('delivery_fee',$settings->delivery_fee) }}" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy sm:col-span-2 xl:col-span-3">Morada<textarea name="address" rows="3" maxlength="5000" class="w-full border-gray-200 text-sm mt-2">{{ old('address',$settings->address) }}</textarea></label>
                </div>
                <button class="bg-gpm-orange text-white text-xs font-bold uppercase px-8 py-4 mt-7">Guardar definições</button>
            </form>
        </section>
    </main>
</div>
@endsection
