<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceOrderDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_admin_deletes_only_the_selected_order_and_its_exclusive_photos(): void
    {
        $order = $this->createOrder();
        $otherOrder = $this->createOrder();
        $product = Product::create(['code' => 'AN001', 'name' => 'Anel', 'category' => 'A']);
        $photo = 'service-order-items/selected.jpg';
        $otherPhoto = 'service-order-items/other.jpg';
        $sharedPhoto = 'service-order-items/shared.jpg';
        $referencePhoto = 'catalog/AN001.jpg';

        $order->items()->create(['product_id' => $product->id, 'product_image' => $photo]);
        $order->items()->create(['product_image' => $sharedPhoto]);
        $order->items()->create(['product_image' => $referencePhoto]);
        $otherItem = $otherOrder->items()->create(['product_image' => $otherPhoto]);
        $sharedItem = $otherOrder->items()->create(['product_image' => $sharedPhoto]);

        $disk = Storage::disk('public');
        foreach ([$photo, $otherPhoto, $sharedPhoto, $referencePhoto] as $path) {
            $disk->put($path, 'imagem de teste');
        }

        $this->actingAs(User::factory()->create(['type' => 'admin']));
        $this->get(route('service-orders.show', $order))
            ->assertOk()
            ->assertSee('Excluir OS')
            ->assertSee('Excluir a OS '.$order->order_number);

        $this->delete(route('service-orders.destroy', $order))
            ->assertRedirect(route('service-orders.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($order);
        $this->assertDatabaseMissing('service_order_items', ['service_order_id' => $order->id]);
        $this->assertModelExists($otherOrder);
        $this->assertModelExists($otherItem);
        $this->assertModelExists($sharedItem);
        $this->assertModelExists($product);
        $disk->assertMissing($photo);
        $disk->assertExists([$otherPhoto, $sharedPhoto, $referencePhoto]);
    }

    public function test_a_non_admin_cannot_delete_an_order_or_its_photo(): void
    {
        $order = $this->createOrder();
        $photo = 'service-order-items/selected.jpg';
        $order->items()->create(['product_image' => $photo]);
        Storage::disk('public')->put($photo, 'imagem de teste');

        $this->actingAs(User::factory()->create(['type' => 'technical']));
        $this->get(route('service-orders.show', $order))->assertOk()->assertDontSee('Excluir OS');
        $this->delete(route('service-orders.destroy', $order))->assertForbidden();

        $this->assertModelExists($order);
        Storage::disk('public')->assertExists($photo);
    }

    private function createOrder(): ServiceOrder
    {
        return ServiceOrder::create([
            'client_name' => 'Cliente teste',
            'consultant_name' => 'Consultora teste',
            'client_code' => '123',
            'origin' => 'SHOWROOM SP',
            'order_date' => '2026-11-26',
        ]);
    }
}
