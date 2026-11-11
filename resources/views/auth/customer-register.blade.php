@extends('layouts.app')

@section('title', 'Criar conta empresarial')

@section('content')
<div class="min-h-screen bg-gpm-soft">
    <header class="bg-white border-b border-gray-100"><div class="max-w-site mx-auto px-6 h-20 flex items-center justify-between"><a href="{{ route('home') }}" class="font-black text-2xl text-gpm-navy">GPM<span class="text-gpm-orange">.</span></a><a href="{{ route('customer.login') }}" class="text-xs font-bold uppercase text-gpm-navy">Já tenho conta</a></div></header>
    <main class="max-w-3xl mx-auto px-5 py-12">
        <form method="post" action="{{ route('customer.register.store') }}" class="bg-white border border-gray-100 shadow-sm p-7 md:p-10">
            @csrf
            <span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Área de cliente</span>
            <h1 class="text-3xl font-black text-gpm-navy mt-3">Criar conta</h1>
            <p class="text-sm text-gray-500 mt-2">Registe os seus dados para enviar pedidos e acompanhar o atendimento da GPM.</p>
            @if($errors->any())<div class="bg-red-50 text-red-700 text-sm p-3 mt-5">Verifique os campos assinalados e tente novamente.</div>@endif
            <div class="grid sm:grid-cols-2 gap-4 mt-7">
                <label class="text-xs font-bold text-gpm-navy">Tipo de conta<select name="customer_type" required class="mt-2 w-full border-gray-200 text-sm"><option value="company" @selected(old('customer_type','company')==='company')>Empresa</option><option value="individual" @selected(old('customer_type')==='individual')>Particular</option></select>@error('customer_type')<span class="text-red-600">{{ $message }}</span>@enderror</label>
                <label class="text-xs font-bold text-gpm-navy">Nome / Empresa<input name="name" value="{{ old('name') }}" required maxlength="180" class="mt-2 w-full border-gray-200 text-sm">@error('name')<span class="text-red-600">{{ $message }}</span>@enderror</label>
                <label class="text-xs font-bold text-gpm-navy">NIF<input name="nif" value="{{ old('nif') }}" maxlength="50" class="mt-2 w-full border-gray-200 text-sm">@error('nif')<span class="text-red-600">{{ $message }}</span>@enderror</label>
                <label class="text-xs font-bold text-gpm-navy">Telefone<input name="phone" value="{{ old('phone') }}" required maxlength="40" class="mt-2 w-full border-gray-200 text-sm">@error('phone')<span class="text-red-600">{{ $message }}</span>@enderror</label>
                <label class="text-xs font-bold text-gpm-navy">Email<input type="email" name="email" value="{{ old('email') }}" required maxlength="190" class="mt-2 w-full border-gray-200 text-sm">@error('email')<span class="text-red-600">{{ $message }}</span>@enderror</label>
                <label class="text-xs font-bold text-gpm-navy">Província<input name="province" value="{{ old('province') }}" maxlength="100" class="mt-2 w-full border-gray-200 text-sm"></label>
                <label class="text-xs font-bold text-gpm-navy sm:col-span-2">Morada<input name="address" value="{{ old('address') }}" class="mt-2 w-full border-gray-200 text-sm"></label>
                <label class="text-xs font-bold text-gpm-navy">Cidade<input name="city" value="{{ old('city') }}" maxlength="100" class="mt-2 w-full border-gray-200 text-sm"></label>
                <label class="text-xs font-bold text-gpm-navy">Palavra-passe<input type="password" name="password" required minlength="8" autocomplete="new-password" class="mt-2 w-full border-gray-200 text-sm">@error('password')<span class="text-red-600">{{ $message }}</span>@enderror</label>
                <label class="text-xs font-bold text-gpm-navy">Confirmar palavra-passe<input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="mt-2 w-full border-gray-200 text-sm"></label>
            </div>
            <button class="w-full sm:w-auto bg-gpm-orange hover:bg-orange-700 text-white font-bold text-xs uppercase tracking-wider px-9 py-4 mt-7" type="submit">Criar conta</button>
            <p class="text-xs text-gray-400 mt-5">Os dados serão usados para gerir os seus pedidos e comunicações comerciais.</p>
        </form>
    </main>
</div>
@endsection
