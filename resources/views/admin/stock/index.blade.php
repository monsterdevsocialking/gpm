@extends('layouts.admin')

@section('title', 'Movimentos de stock')

@section('content')
<div class="min-h-screen lg:grid lg:grid-cols-[250px_1fr]">
    <aside class="bg-gpm-navy text-white p-6"><a href="{{ route('admin.dashboard') }}" class="font-black text-2xl">GPM<span class="text-gpm-orange">.</span></a><p class="text-[10px] uppercase tracking-[.2em] text-white/45 mt-2">Painel interno</p><nav class="mt-10 space-y-2 text-sm"><a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Visão geral</a>@if(auth()->user()->role === 'admin')<a href="{{ route('admin.catalog.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Produtos</a><a href="{{ route('admin.categories.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Categorias</a>@endif<a href="{{ route('admin.stock.index') }}" class="block px-4 py-3 bg-white/10 text-white"><i class="fa-solid fa-boxes-stacked text-gpm-orange mr-2"></i>Movimentos de stock</a>@if(auth()->user()->role === 'admin')<a href="{{ route('admin.orders.index') }}" class="block px-4 py-3 text-white/70 hover:bg-white/10">Encomendas</a>@endif</nav><form method="post" action="{{ route('admin.logout') }}" class="mt-10">@csrf<button class="text-xs font-bold uppercase text-white/60">Terminar sessão</button></form></aside>
    <main class="p-5 md:p-8 lg:p-10">
        <div class="mb-7"><span class="text-[10px] font-extrabold tracking-[.2em] uppercase text-gpm-orange">Controlo de inventário</span><h1 class="text-3xl font-black text-gpm-navy mt-2">Movimentos de stock</h1><p class="text-sm text-gray-500 mt-1">Todas as entradas, saídas e correções ficam registadas com o responsável e o saldo anterior e posterior.</p></div>
        @if(session('success'))<div class="bg-green-50 text-green-800 text-sm p-4 mb-6">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="bg-red-50 text-red-700 text-sm p-4 mb-6">{{ $errors->first() }}</div>@endif
        <section class="bg-white border border-gray-100 p-6 mb-7">
            <h2 class="font-black text-gpm-navy mb-5">Registar movimento</h2>
            <form method="post" action="{{ route('admin.stock.store') }}">@csrf
                <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    <label class="text-xs font-bold text-gpm-navy xl:col-span-2">Produto<select name="product_id" required class="w-full border-gray-200 text-sm mt-2"><option value="">Selecionar produto</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected((string)old('product_id')===(string)$product->id)>{{ $product->name }} · Stock: {{ number_format((float)$product->stock_quantity,3,',',' ') }} {{ $product->unit }}</option>@endforeach</select></label>
                    <label class="text-xs font-bold text-gpm-navy">Tipo<select name="movement_type" id="movement_type" required class="w-full border-gray-200 text-sm mt-2"><option value="entry" @selected(old('movement_type')==='entry')>Entrada</option><option value="exit" @selected(old('movement_type')==='exit')>Saída</option><option value="adjustment" @selected(old('movement_type')==='adjustment')>Ajustar saldo final</option></select></label>
                    <label class="text-xs font-bold text-gpm-navy"><span id="quantity-label">Quantidade</span><input type="number" name="quantity" min="0" step="0.001" required value="{{ old('quantity') }}" class="w-full border-gray-200 text-sm mt-2"></label>
                    <label class="text-xs font-bold text-gpm-navy sm:col-span-2 xl:col-span-4">Motivo / observação<input name="reason" maxlength="255" value="{{ old('reason') }}" placeholder="Ex.: compra ao fornecedor, entrega, correção de inventário..." class="w-full border-gray-200 text-sm mt-2"></label>
                </div>
                <button class="bg-gpm-orange text-white text-xs font-bold uppercase px-7 py-4 mt-5">Registar movimento</button>
            </form>
        </section>
        <section class="bg-white border border-gray-100">
            <div class="p-5 border-b border-gray-100"><h2 class="font-black text-gpm-navy">Histórico de movimentos</h2><p class="text-xs text-gray-400 mt-1">Registos mais recentes primeiro.</p></div>
            <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-gray-50 text-[10px] uppercase text-gray-500"><tr><th class="text-left p-4">Data</th><th class="text-left p-4">Produto</th><th class="text-left p-4">Tipo</th><th class="text-left p-4">Quantidade</th><th class="text-left p-4">Antes</th><th class="text-left p-4">Depois</th><th class="text-left p-4">Responsável</th><th class="text-left p-4">Motivo</th></tr></thead><tbody>
                @forelse($movements as $movement)
                    @php($labels = ['entry'=>'Entrada','exit'=>'Saída','sale'=>'Venda','sale_return'=>'Devolução de venda','adjustment_in'=>'Ajuste positivo','adjustment_out'=>'Ajuste negativo'])
                    <tr class="border-t border-gray-100"><td class="p-4 text-gray-400 whitespace-nowrap">{{ optional($movement->created_at)->format('d/m/Y H:i') }}</td><td class="p-4"><strong class="text-gpm-navy">{{ $movement->product?->name ?: 'Produto removido' }}</strong><p class="text-[10px] text-gray-400">{{ $movement->product?->sku }}</p></td><td class="p-4"><span class="text-[10px] font-bold uppercase {{ in_array($movement->movement_type,['entry','sale_return','adjustment_in'],true) ? 'text-emerald-700' : 'text-red-600' }}">{{ $labels[$movement->movement_type] ?? $movement->movement_type }}</span></td><td class="p-4">{{ number_format((float)$movement->quantity,3,',',' ') }}</td><td class="p-4">{{ number_format((float)$movement->stock_before,3,',',' ') }}</td><td class="p-4 font-bold text-gpm-navy">{{ number_format((float)$movement->stock_after,3,',',' ') }}</td><td class="p-4">{{ $movement->user?->name ?: '—' }}</td><td class="p-4 text-gray-500">{{ $movement->reason ?: '—' }}</td></tr>
                @empty<tr><td colspan="8" class="p-10 text-center text-gray-400">Ainda não existem movimentos de stock.</td></tr>@endforelse
            </tbody></table></div>
            <div class="p-5">{{ $movements->links() }}</div>
        </section>
    </main>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('movement_type');
    const label = document.getElementById('quantity-label');
    function updateLabel() {
        label.textContent = type.value === 'adjustment' ? 'Novo saldo final' : 'Quantidade';
    }
    type.addEventListener('change', updateLabel);
    updateLabel();
});
</script>
@endpush
