@extends('layouts.admin')

@section('title', 'Fornecedores')

@section('content')
<div class="min-h-screen lg:grid lg:grid-cols-[250px_1fr]">
    <aside class="bg-gpm-navy text-white p-6"><a href="{{ route('admin.dashboard') }}" class="font-black text-2xl">GPM<span class="text-gpm-orange">.</span></a><p class="text-[10px] uppercase tracking-[.2em] text-white/45 mt-2">Painel interno</p><nav class="mt-10 space-y-2 text-sm"><a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Visão geral</a><a href="{{ route('admin.requests.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Pedidos de fornecimento</a><a href="{{ route('admin.suppliers.index') }}" class="block px-4 py-3 bg-white/10 text-white"><i class="fa-solid fa-truck-field text-gpm-orange mr-2"></i>Fornecedores</a></nav><form method="post" action="{{ route('admin.logout') }}" class="mt-10">@csrf<button class="text-xs font-bold uppercase text-white/60">Terminar sessão</button></form></aside>
    <main class="p-5 md:p-8 lg:p-10">
        <div class="mb-7"><span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Rede de fornecimento</span><h1 class="text-3xl font-black text-gpm-navy mt-2">Fornecedores</h1><p class="text-sm text-gray-500 mt-1">Registe e mantenha os contactos usados nas consultas comerciais.</p></div>
        @if(session('success'))<div class="bg-green-50 text-green-800 text-sm p-4 mb-6">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="bg-red-50 text-red-700 text-sm p-4 mb-6">{{ $errors->first() }}</div>@endif
        <section class="bg-white border border-gray-100 p-6 mb-7">
            <h2 class="font-black text-gpm-navy mb-5">Adicionar fornecedor</h2>
            <form method="post" action="{{ route('admin.suppliers.store') }}">@csrf
                <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    <label class="text-xs font-bold text-gpm-navy">Nome / Empresa *<input name="name" required maxlength="180" value="{{ old('name') }}" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy">NIF<input name="nif" maxlength="50" value="{{ old('nif') }}" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy">Pessoa de contacto<input name="contact_name" maxlength="150" value="{{ old('contact_name') }}" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy">Email<input type="email" name="email" maxlength="190" value="{{ old('email') }}" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy">Telefone<input name="phone" maxlength="40" value="{{ old('phone') }}" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy">Website<input type="url" name="website" maxlength="255" value="{{ old('website') }}" placeholder="https://" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy">Cidade<input name="city" maxlength="100" value="{{ old('city') }}" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy">País<input name="country" maxlength="100" value="{{ old('country','Angola') }}" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy sm:col-span-2 xl:col-span-3">Morada<textarea name="address" rows="2" class="w-full border-gray-200 text-sm mt-2">{{ old('address') }}</textarea></label>
                    <label class="text-xs font-bold text-gpm-navy sm:col-span-2 xl:col-span-3">Notas internas<textarea name="notes" rows="2" class="w-full border-gray-200 text-sm mt-2">{{ old('notes') }}</textarea></label>
                </div>
                <button class="bg-gpm-orange text-white font-bold text-xs uppercase px-7 py-4 mt-5">Guardar fornecedor</button>
            </form>
        </section>
        <form method="get" class="bg-white border border-gray-100 p-4 flex gap-3 mb-4"><input name="q" value="{{ $search }}" placeholder="Pesquisar nome, NIF ou contacto" class="flex-1 border-gray-200 text-sm"><button class="bg-gpm-navy text-white text-xs font-bold uppercase px-6">Pesquisar</button></form>
        <div class="space-y-3">
            @forelse($suppliers as $supplier)
                <details class="bg-white border border-gray-100 group">
                    <summary class="cursor-pointer list-none p-5 flex items-center justify-between gap-4"><div><strong class="text-sm text-gpm-navy">{{ $supplier->name }}</strong><p class="text-xs text-gray-400 mt-1">{{ $supplier->contact_name ?: 'Sem contacto definido' }} · {{ $supplier->phone ?: 'Sem telefone' }}</p></div><div class="flex items-center gap-4"><span class="text-[10px] font-bold uppercase {{ $supplier->active ? 'text-emerald-700' : 'text-gray-400' }}">{{ $supplier->active ? 'Ativo' : 'Inativo' }}</span><i class="fa-solid fa-chevron-down text-gray-400"></i></div></summary>
                    <div class="border-t border-gray-100 p-5">
                        <form method="post" action="{{ route('admin.suppliers.update',$supplier) }}">@csrf @method('PUT')
                            <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
                                @foreach(['name'=>'Nome / Empresa','nif'=>'NIF','contact_name'=>'Contacto','email'=>'Email','phone'=>'Telefone','website'=>'Website','city'=>'Cidade','country'=>'País'] as $field=>$label)
                                    <label class="text-xs font-bold text-gpm-navy">{{ $label }}<input name="{{ $field }}" value="{{ $supplier->{$field} }}" class="w-full border-gray-200 text-sm mt-2"></label>
                                @endforeach
                                <label class="text-xs font-bold text-gpm-navy sm:col-span-2 xl:col-span-3">Morada<textarea name="address" rows="2" class="w-full border-gray-200 text-sm mt-2">{{ $supplier->address }}</textarea></label>
                                <label class="text-xs font-bold text-gpm-navy sm:col-span-2 xl:col-span-3">Notas<textarea name="notes" rows="2" class="w-full border-gray-200 text-sm mt-2">{{ $supplier->notes }}</textarea></label>
                                <label class="flex items-center gap-2 text-xs font-bold text-gpm-navy"><input type="checkbox" name="active" value="1" @checked($supplier->active)> Fornecedor ativo</label>
                            </div>
                            <button class="bg-gpm-navy text-white text-xs font-bold uppercase px-6 py-3 mt-4">Guardar alterações</button>
                        </form>
                    </div>
                </details>
            @empty
                <div class="bg-white border border-gray-100 p-10 text-center text-sm text-gray-400">Ainda não existem fornecedores registados.</div>
            @endforelse
        </div>
        <div class="mt-6">{{ $suppliers->links() }}</div>
    </main>
</div>
@endsection
