@extends('layouts.admin')

@section('title', 'Categorias')

@section('content')
<div class="min-h-screen lg:grid lg:grid-cols-[250px_1fr]">
    <aside class="bg-gpm-navy text-white p-6"><a href="{{ route('admin.dashboard') }}" class="font-black text-2xl">GPM<span class="text-gpm-orange">.</span></a><p class="text-[10px] uppercase tracking-[.2em] text-white/45 mt-2">Painel interno</p><nav class="mt-10 space-y-2 text-sm"><a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Visão geral</a><a href="{{ route('admin.catalog.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Produtos</a><a href="{{ route('admin.categories.index') }}" class="block px-4 py-3 bg-white/10 text-white"><i class="fa-solid fa-layer-group text-gpm-orange mr-2"></i>Categorias</a><a href="{{ route('admin.requests.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Pedidos de fornecimento</a><a href="{{ route('admin.suppliers.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Fornecedores</a><a href="{{ route('admin.orders.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Encomendas</a></nav><form method="post" action="{{ route('admin.logout') }}" class="mt-10">@csrf<button class="text-xs font-bold uppercase text-white/60">Terminar sessão</button></form></aside>
    <main class="p-5 md:p-8 lg:p-10">
        <div class="mb-7"><span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Gestão do catálogo</span><h1 class="text-3xl font-black text-gpm-navy mt-2">Categorias</h1><p class="text-sm text-gray-500 mt-1">Organize o catálogo sem apagar categorias que já tenham produtos associados.</p></div>
        @if(session('success'))<div class="bg-green-50 text-green-800 text-sm p-4 mb-6">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="bg-red-50 text-red-700 text-sm p-4 mb-6">{{ $errors->first() }}</div>@endif
        <section class="bg-white border border-gray-100 p-6 mb-7">
            <h2 class="font-black text-gpm-navy mb-5">{{ $editing ? 'Editar categoria' : 'Nova categoria' }}</h2>
            <form method="post" action="{{ $editing ? route('admin.categories.update',$editing) : route('admin.categories.store') }}">
                @csrf @if($editing) @method('PUT') @endif
                <div class="grid md:grid-cols-2 gap-4">
                    <label class="text-xs font-bold text-gpm-navy">Nome<input name="name" required maxlength="120" value="{{ old('name',$editing?->name) }}" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy">Slug<input name="slug" maxlength="150" value="{{ old('slug',$editing?->slug) }}" placeholder="Gerado automaticamente se vazio" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy md:col-span-2">Descrição<textarea name="description" rows="3" maxlength="5000" class="w-full border-gray-200 text-sm mt-2">{{ old('description',$editing?->description) }}</textarea></label>
                    <label class="flex items-center gap-2 text-xs font-bold text-gpm-navy"><input type="hidden" name="active" value="0"><input type="checkbox" name="active" value="1" @checked(old('active',$editing?->active ?? true))> Categoria ativa</label>
                </div>
                <div class="flex gap-3 mt-5"><button class="bg-gpm-orange text-white text-xs font-bold uppercase px-7 py-4">{{ $editing ? 'Guardar alterações' : 'Criar categoria' }}</button>@if($editing)<a href="{{ route('admin.categories.index') }}" class="border border-gray-200 text-gray-600 text-xs font-bold uppercase px-7 py-4">Cancelar</a>@endif</div>
            </form>
        </section>
        <section class="bg-white border border-gray-100">
            <div class="p-5 border-b border-gray-100"><h2 class="font-black text-gpm-navy">Categorias registadas</h2></div>
            <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-gray-50 text-[10px] uppercase text-gray-500"><tr><th class="text-left p-4">Nome</th><th class="text-left p-4">Slug</th><th class="text-left p-4">Produtos</th><th class="text-left p-4">Estado</th><th class="text-right p-4">Ações</th></tr></thead><tbody>
                @forelse($categories as $category)
                    <tr class="border-t border-gray-100"><td class="p-4 font-bold text-gpm-navy">{{ $category->name }}</td><td class="p-4 text-gray-500">{{ $category->slug }}</td><td class="p-4">{{ $category->products_count }}</td><td class="p-4"><span class="text-[10px] uppercase font-bold {{ $category->active ? 'text-emerald-700' : 'text-gray-400' }}">{{ $category->active ? 'Ativa' : 'Inativa' }}</span></td><td class="p-4"><div class="flex justify-end gap-3"><a href="{{ route('admin.categories.index',['edit'=>$category->id]) }}" class="text-xs font-bold uppercase text-gpm-orange">Editar</a><form method="post" action="{{ route('admin.categories.destroy',$category) }}" onsubmit="return confirm('Eliminar ou desativar esta categoria?')">@csrf @method('DELETE')<button class="text-xs font-bold uppercase text-red-600">Eliminar</button></form></div></td></tr>
                @empty<tr><td colspan="5" class="p-8 text-center text-sm text-gray-400">Ainda não existem categorias.</td></tr>@endforelse
            </tbody></table></div>
            <div class="p-5">{{ $categories->links() }}</div>
        </section>
    </main>
</div>
@endsection
