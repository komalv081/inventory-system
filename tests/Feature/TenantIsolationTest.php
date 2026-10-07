<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_another_shop_product(): void
    {
        $shopA = Shop::factory()->create();
        $shopB = Shop::factory()->create();

        $userA = User::factory()->create();
        $userA->shops()->attach($shopA->id, ['role' => 'owner']);

        $productB = Product::factory()->create([
            'shop_id' => $shopB->id,
            'barcode' => '1234567890123',
            'product_name' => 'Other Shop Product',
        ]);

        $this->actingAs($userA, 'sanctum');

        $this->getJson('/api/v1/products/' . $productB->id)
            ->assertStatus(403);
    }

    public function test_user_cannot_update_another_shop_product(): void
    {
        $shopA = Shop::factory()->create();
        $shopB = Shop::factory()->create();

        $userA = User::factory()->create();
        $userA->shops()->attach($shopA->id, ['role' => 'owner']);

        $productB = Product::factory()->create([
            'shop_id' => $shopB->id,
            'barcode' => '1111111111111',
            'product_name' => 'Protected Product',
        ]);

        $this->actingAs($userA, 'sanctum');

        $this->putJson('/api/v1/products/' . $productB->id, [
            'product_name' => 'Tampered name',
        ])->assertStatus(403);
    }
}
