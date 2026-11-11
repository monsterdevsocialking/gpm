<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_entries_exits_and_adjustments_are_atomic_and_audited(): void
    {
        $staff = User::query()->create([
            'name' => 'Gestor de Stock',
            'email' => 'stock@example.test',
            'password' => 'password',
        ]);
        $staff->forceFill(['role' => 'stock_manager', 'active' => true])->save();

        $product = Product::query()->create([
            'name' => 'Produto de teste',
            'slug' => 'produto-de-teste',
            'price' => 100,
            'stock_quantity' => 0,
            'min_stock' => 1,
            'unit' => 'unidade',
            'active' => true,
        ]);

        $this->actingAs($staff, 'web')
            ->post(route('admin.stock.store'), [
                'product_id' => $product->id,
                'movement_type' => 'entry',
                'quantity' => 10,
                'reason' => 'Receção de fornecedor',
            ])
            ->assertRedirect(route('admin.stock.index'));

        $this->assertEquals(10.0, (float) $product->fresh()->stock_quantity);

        $this->actingAs($staff, 'web')
            ->post(route('admin.stock.store'), [
                'product_id' => $product->id,
                'movement_type' => 'exit',
                'quantity' => 4,
                'reason' => 'Saída autorizada',
            ])
            ->assertRedirect(route('admin.stock.index'));

        $this->assertEquals(6.0, (float) $product->fresh()->stock_quantity);

        $this->actingAs($staff, 'web')
            ->post(route('admin.stock.store'), [
                'product_id' => $product->id,
                'movement_type' => 'adjustment',
                'quantity' => 2,
                'reason' => 'Contagem física',
            ])
            ->assertRedirect(route('admin.stock.index'));

        $this->assertEquals(2.0, (float) $product->fresh()->stock_quantity);
        $this->assertSame(3, StockMovement::query()->where('product_id', $product->id)->count());

        $this->actingAs($staff, 'web')
            ->from(route('admin.stock.index'))
            ->post(route('admin.stock.store'), [
                'product_id' => $product->id,
                'movement_type' => 'exit',
                'quantity' => 3,
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertEquals(2.0, (float) $product->fresh()->stock_quantity);
        $this->assertSame(3, StockMovement::query()->where('product_id', $product->id)->count());
    }
}
