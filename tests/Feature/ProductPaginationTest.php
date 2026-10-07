<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_list_returns_paginated_data(): void
    {
        $shop = Shop::factory()->create();
        $user = User::factory()->create();
        $user->shops()->attach($shop->id, ['role' => 'owner']);

        Product::factory()->count(18)->create([
            'shop_id' => $shop->id,
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/products?per_page=15&page=2');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.total', 18)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonStructure(['links' => ['first', 'prev', 'next', 'last']]);
    }

    public function test_product_page_size_is_capped(): void
    {
        $shop = Shop::factory()->create();
        $user = User::factory()->create();
        $user->shops()->attach($shop->id, ['role' => 'owner']);

        Product::factory()->count(60)->create([
            'shop_id' => $shop->id,
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/products?per_page=100');

        $response->assertOk()
            ->assertJsonCount(50, 'data')
            ->assertJsonPath('meta.per_page', 50);
    }
}
