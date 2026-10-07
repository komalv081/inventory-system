<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_an_order_for_review_without_changing_inventory(): void
    {
        $shop = Shop::factory()->create(['name' => 'ABC Grocery Store']);
        $user = User::factory()->create();
        $user->shops()->attach($shop->id, ['role' => 'owner']);
        $supplier = Supplier::factory()->create([
            'shop_id' => $shop->id,
            'name' => 'ABC Wholesale',
            'phone' => '+91 90000 00001',
            'whatsapp_phone' => '+91 90000 00002',
        ]);
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'product_name' => 'Maggi 70g',
            'current_quantity' => 8,
            'unit' => 'pcs',
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/purchase-orders', [
                'supplier_id' => $supplier->id,
                'items' => [['product_id' => $product->id, 'quantity' => 30]],
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.supplier.name', 'ABC Wholesale')
            ->assertJsonPath('data.items.0.product_name', 'Maggi 70g')
            ->assertJsonPath('data.items.0.quantity', 30)
            ->assertJsonPath('data.message', fn (string $message) => str_contains($message, 'Maggi 70g - 30 pcs'));

        $product->update(['product_name' => 'Renamed product']);

        $this->assertDatabaseHas('purchase_order_items', [
            'product_id' => $product->id,
            'product_name_snapshot' => 'Maggi 70g',
            'quantity' => 30,
        ]);
        $this->assertSame(8, $product->fresh()->current_quantity);
        $this->assertDatabaseCount('purchase_orders', 1);
    }

    public function test_order_creation_rejects_foreign_supplier_and_product_without_partial_order(): void
    {
        $shopA = Shop::factory()->create();
        $shopB = Shop::factory()->create();
        $user = User::factory()->create();
        $user->shops()->attach($shopA->id, ['role' => 'owner']);
        $supplierB = Supplier::factory()->create(['shop_id' => $shopB->id]);
        $productA = Product::factory()->create(['shop_id' => $shopA->id]);
        $productB = Product::factory()->create(['shop_id' => $shopB->id]);

        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/v1/purchase-orders', [
            'supplier_id' => $supplierB->id,
            'items' => [['product_id' => $productA->id, 'quantity' => 2]],
        ])->assertUnprocessable();

        $supplierA = Supplier::factory()->create(['shop_id' => $shopA->id]);
        $this->postJson('/api/v1/purchase-orders', [
            'supplier_id' => $supplierA->id,
            'items' => [['product_id' => $productB->id, 'quantity' => 2]],
        ])->assertUnprocessable();

        $this->assertDatabaseCount('purchase_orders', 0);
        $this->assertDatabaseCount('purchase_order_items', 0);
    }

    public function test_owner_can_edit_message_and_save_the_order_as_a_draft(): void
    {
        [$user, $supplier, $product] = $this->shopOrderData();
        $this->actingAs($user, 'sanctum');

        $order = $this->createOrder($supplier, $product);

        $this->putJson('/api/v1/purchase-orders/' . $order . '/message', [
            'message' => 'Edited order request',
        ])->assertOk()->assertJsonPath('data.status', 'reviewed')
            ->assertJsonPath('data.message', 'Edited order request');

        $this->putJson('/api/v1/purchase-orders/' . $order . '/draft', [
            'message' => 'Saved draft message',
        ])->assertOk()->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.message', 'Saved draft message');
    }

    public function test_whatsapp_and_sms_handoff_record_their_actual_delivery_status(): void
    {
        [$user, $supplier, $product] = $this->shopOrderData();
        $this->actingAs($user, 'sanctum');
        $order = $this->createOrder($supplier, $product);

        $this->postJson('/api/v1/purchase-orders/' . $order . '/handoff', [
            'channel' => 'whatsapp',
            'message' => 'WhatsApp reviewed text',
        ])->assertOk()
            ->assertJsonPath('handoff_status', 'opened')
            ->assertJsonPath('data.communications.0.status', 'opened')
            ->assertJsonPath('data.communications.0.channel', 'whatsapp')
            ->assertJsonPath('data.communications.0.message', 'WhatsApp reviewed text')
            ->assertJsonPath('handoff_url', fn (string $url) =>
                str_starts_with($url, 'https://wa.me/919000000002?text=')
            );

        $this->postJson('/api/v1/purchase-orders/' . $order . '/handoff', [
            'channel' => 'sms',
            'message' => 'SMS reviewed text',
        ])->assertOk()
            ->assertJsonPath('handoff_status', 'opened')
            ->assertJsonPath('data.communications.1.status', 'opened')
            ->assertJsonPath('data.communications.1.channel', 'sms')
            ->assertJsonPath('handoff_url', fn (string $url) => str_starts_with($url, 'sms:'));

        $this->assertDatabaseCount('supplier_communications', 2);
        $this->assertDatabaseMissing('supplier_communications', ['status' => 'sent']);
    }

    public function test_empty_items_invalid_quantities_inactive_suppliers_and_missing_numbers_are_rejected(): void
    {
        [$user, $supplier, $product] = $this->shopOrderData();
        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/v1/purchase-orders', [
            'supplier_id' => $supplier->id,
            'items' => [],
        ])->assertUnprocessable()->assertJsonValidationErrors('items');

        $this->postJson('/api/v1/purchase-orders', [
            'supplier_id' => $supplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 0]],
        ])->assertUnprocessable()->assertJsonValidationErrors('items.0.quantity');

        $inactiveSupplier = Supplier::factory()->create([
            'shop_id' => $supplier->shop_id,
            'is_active' => false,
        ]);
        $this->postJson('/api/v1/purchase-orders', [
            'supplier_id' => $inactiveSupplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertUnprocessable()->assertJsonPath('errors.supplier_id.0', 'Choose an active supplier belonging to your shop.');

        $order = $this->createOrder($supplier, $product);
        $supplier->update(['whatsapp_phone' => null]);

        $this->postJson('/api/v1/purchase-orders/' . $order . '/handoff', [
            'channel' => 'whatsapp',
            'message' => 'Order request',
        ])->assertUnprocessable();
        $this->postJson('/api/v1/purchase-orders/' . $order . '/handoff', [
            'channel' => 'sms',
            'message' => '',
        ])->assertUnprocessable()->assertJsonValidationErrors('message');

        $this->assertDatabaseCount('supplier_communications', 0);
    }

    public function test_order_history_and_mutations_are_restricted_to_the_users_shop(): void
    {
        $shopA = Shop::factory()->create();
        $shopB = Shop::factory()->create();
        $userA = User::factory()->create();
        $userA->shops()->attach($shopA->id, ['role' => 'owner']);
        $supplierB = Supplier::factory()->create(['shop_id' => $shopB->id]);
        $productB = Product::factory()->create(['shop_id' => $shopB->id]);
        $orderB = \App\Models\PurchaseOrder::create([
            'shop_id' => $shopB->id,
            'supplier_id' => $supplierB->id,
            'status' => 'draft',
        ]);
        $orderB->items()->create([
            'product_id' => $productB->id,
            'quantity' => 1,
            'product_name_snapshot' => $productB->product_name,
        ]);

        $this->actingAs($userA, 'sanctum');

        $this->getJson('/api/v1/purchase-orders')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/purchase-orders/' . $orderB->id)->assertForbidden();
        $this->putJson('/api/v1/purchase-orders/' . $orderB->id . '/message', [
            'message' => 'Attempted change',
        ])->assertForbidden();
        $this->postJson('/api/v1/purchase-orders/' . $orderB->id . '/cancel')->assertForbidden();
    }

    public function test_owner_can_delete_draft_orders_but_not_sent_orders(): void
    {
        [$user, $supplier, $product] = $this->shopOrderData();
        $this->actingAs($user, 'sanctum');

        $draftOrder = $this->createOrder($supplier, $product);
        $sentOrderId = $this->createOrder($supplier, $product);
        \App\Models\PurchaseOrder::query()->whereKey($sentOrderId)->update(['status' => 'sent']);

        $this->deleteJson('/api/v1/purchase-orders/' . $draftOrder)
            ->assertOk()
            ->assertJsonPath('message', 'Purchase order deleted successfully.');

        $this->assertDatabaseMissing('purchase_orders', ['id' => $draftOrder]);
        $this->deleteJson('/api/v1/purchase-orders/' . $sentOrderId)->assertForbidden();
    }

    public function test_purchase_order_web_workflow_pages_render(): void
    {
        $this->get('/purchase-orders')->assertOk()->assertSee('Create order');
        $this->get('/purchase-orders/create')->assertOk()->assertSee('Review order')->assertSee('Send via WhatsApp');
        $this->get('/purchase-orders/1')->assertOk()->assertSee('Order details');
    }

    public function test_selectors_only_return_products_and_suppliers_from_default_shop(): void
    {
        $shopA = Shop::factory()->create();
        $shopB = Shop::factory()->create();
        $user = User::factory()->create();
        $user->shops()->attach($shopA->id, ['role' => 'owner']);
        $user->shops()->attach($shopB->id, ['role' => 'owner']);

        Product::factory()->create(['shop_id' => $shopA->id, 'product_name' => 'Shop A product', 'is_active' => true]);
        Product::factory()->create(['shop_id' => $shopB->id, 'product_name' => 'Shop B product', 'is_active' => true]);
        Product::factory()->create(['shop_id' => $shopA->id, 'product_name' => 'Archived product', 'is_active' => false]);
        Supplier::factory()->create(['shop_id' => $shopA->id, 'name' => 'Shop A supplier', 'is_active' => true]);
        Supplier::factory()->create(['shop_id' => $shopA->id, 'name' => 'Inactive supplier', 'is_active' => false]);
        Supplier::factory()->create(['shop_id' => $shopB->id, 'name' => 'Shop B supplier', 'is_active' => true]);

        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/v1/purchase-orders/products')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.product_name', 'Shop A product');
        $this->getJson('/api/v1/purchase-orders/suppliers')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Shop A supplier');
        $this->getJson('/api/v1/purchase-orders/suppliers?include_inactive=true')
            ->assertOk()->assertJsonCount(2, 'data')->assertJsonMissing(['name' => 'Shop B supplier']);
    }

    private function shopOrderData(): array
    {
        $shop = Shop::factory()->create();
        $user = User::factory()->create();
        $user->shops()->attach($shop->id, ['role' => 'owner']);
        $supplier = Supplier::factory()->create([
            'shop_id' => $shop->id,
            'phone' => '+91 90000 00001',
            'whatsapp_phone' => '+91 90000 00002',
        ]);
        $product = Product::factory()->create(['shop_id' => $shop->id]);

        return [$user, $supplier, $product];
    }

    private function createOrder(Supplier $supplier, Product $product): int
    {
        $response = $this->postJson('/api/v1/purchase-orders', [
            'supplier_id' => $supplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
        ]);

        $response->assertCreated();

        return $response->json('data.id');
    }
}
