<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_images_initial_stock_and_historical_deactivation_are_safe(): void
    {
        Storage::fake('public');

        $admin = User::query()->create([
            'name' => 'Administrador',
            'email' => 'catalog-admin@example.test',
            'password' => 'password',
        ]);
        $admin->forceFill(['role' => 'admin', 'active' => true])->save();

        $this->actingAs($admin, 'web')
            ->post(route('admin.catalog.store'), [
                'name' => 'Bomba industrial',
                'slug' => '',
                'sku' => 'BOM-001',
                'price' => 150000,
                'min_stock' => 2,
                'initial_stock' => 5,
                'unit' => 'unidade',
                'active' => 1,
                'featured' => 1,
                'main_image' => UploadedFile::fake()->image('bomba.png'),
                'extra_images' => [
                    UploadedFile::fake()->image('bomba-detalhe.png'),
                ],
            ])
            ->assertRedirect(route('admin.catalog.index'));

        $product = Product::query()->where('sku', 'BOM-001')->firstOrFail();

        $this->assertSame('bomba-industrial', $product->slug);
        $this->assertEquals(5.0, (float) $product->stock_quantity);
        $this->assertSame(1, $product->images()->count());
        Storage::disk('public')->assertExists($product->main_image);
        Storage::disk('public')->assertExists($product->images()->first()->image_path);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'entry',
            'reason' => 'Stock inicial do produto',
        ]);

        $this->actingAs($admin, 'web')
            ->put(route('admin.catalog.update', $product), [
                'name' => 'Bomba industrial atualizada',
                'slug' => 'bomba-industrial',
                'sku' => 'BOM-001',
                'price' => 160000,
                'min_stock' => 3,
                'unit' => 'unidade',
                'active' => 1,
            ])
            ->assertRedirect(route('admin.catalog.index'));

        $this->assertEquals(5.0, (float) $product->fresh()->stock_quantity);
        $this->assertEquals(160000.0, (float) $product->fresh()->price);

        $this->actingAs($admin, 'web')
            ->delete(route('admin.catalog.destroy', $product))
            ->assertRedirect(route('admin.catalog.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'active' => 0,
        ]);
        $this->assertSame(1, StockMovement::query()->where('product_id', $product->id)->count());
    }
}
