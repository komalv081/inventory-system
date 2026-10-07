<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_supplier(): void
    {
        $shop = Shop::factory()->create();
        $user = User::factory()->create();
        $user->shops()->attach($shop->id, ['role' => 'owner']);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/v1/suppliers', [
            'name' => 'ABC Wholesale',
            'company_name' => 'ABC Traders',
            'phone' => '+91 90000 00001',
            'whatsapp_phone' => '+91 90000 00002',
            'email' => 'abc@example.com',
            'address' => 'Market Road',
            'notes' => 'Fast delivery',
            'is_active' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'ABC Wholesale');

        $this->assertDatabaseHas('suppliers', [
            'shop_id' => $shop->id,
            'name' => 'ABC Wholesale',
        ]);
    }

    public function test_leading_zero_indian_numbers_are_saved_in_international_format(): void
    {
        $shop = Shop::factory()->create();
        $user = User::factory()->create();
        $user->shops()->attach($shop->id, ['role' => 'owner']);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/suppliers', [
                'name' => 'Indian Supplier',
                'phone' => '09632778755',
                'whatsapp_phone' => '09632778755',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.phone', '+919632778755')
            ->assertJsonPath('data.whatsapp_phone', '+919632778755');

        $this->assertDatabaseHas('suppliers', [
            'phone' => '+919632778755',
            'whatsapp_phone' => '+919632778755',
        ]);
    }

    public function test_supplier_name_and_phone_are_required(): void
    {
        $shop = Shop::factory()->create();
        $user = User::factory()->create();
        $user->shops()->attach($shop->id, ['role' => 'owner']);

        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/v1/suppliers', [
            'company_name' => 'Acme',
            'email' => 'bad-email',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'phone']);
    }

    public function test_owner_can_view_their_supplier(): void
    {
        $shop = Shop::factory()->create();
        $user = User::factory()->create();
        $user->shops()->attach($shop->id, ['role' => 'owner']);

        $supplier = Supplier::factory()->create([
            'shop_id' => $shop->id,
            'name' => 'Reliable Imports',
            'phone' => '+91 90000 00003',
        ]);

        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/v1/suppliers/' . $supplier->id)
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Reliable Imports');
    }

    public function test_shop_a_cannot_view_or_update_shop_b_supplier(): void
    {
        $shopA = Shop::factory()->create();
        $shopB = Shop::factory()->create();
        $userA = User::factory()->create();
        $userA->shops()->attach($shopA->id, ['role' => 'owner']);

        $supplierB = Supplier::factory()->create([
            'shop_id' => $shopB->id,
            'name' => 'B Supplier',
            'phone' => '+91 90000 00004',
        ]);

        $this->actingAs($userA, 'sanctum');

        $this->getJson('/api/v1/suppliers/' . $supplierB->id)->assertStatus(403);
        $this->putJson('/api/v1/suppliers/' . $supplierB->id, [
            'name' => 'Hacked',
            'phone' => '+91 90000 00005',
        ])->assertStatus(403);
    }

    public function test_search_only_returns_suppliers_for_authenticated_shop(): void
    {
        $shopA = Shop::factory()->create();
        $shopB = Shop::factory()->create();
        $userA = User::factory()->create();
        $userA->shops()->attach($shopA->id, ['role' => 'owner']);

        Supplier::factory()->create([
            'shop_id' => $shopA->id,
            'name' => 'Alpha Importer',
            'phone' => '+91 90000 00006',
            'is_active' => true,
        ]);

        Supplier::factory()->create([
            'shop_id' => $shopB->id,
            'name' => 'Beta Importer',
            'phone' => '+91 90000 00007',
            'is_active' => true,
        ]);

        $this->actingAs($userA, 'sanctum');

        $response = $this->getJson('/api/v1/suppliers?search=Importer');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonMissingPath('data.1');
    }

    public function test_deactivated_supplier_is_hidden_from_active_results_and_can_be_reactivated(): void
    {
        $shop = Shop::factory()->create();
        $user = User::factory()->create();
        $user->shops()->attach($shop->id, ['role' => 'owner']);

        $supplier = Supplier::factory()->create([
            'shop_id' => $shop->id,
            'name' => 'Inactive Supplier',
            'phone' => '+91 90000 00008',
            'is_active' => true,
        ]);

        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/v1/suppliers/' . $supplier->id . '/deactivate')
            ->assertStatus(200);

        $this->getJson('/api/v1/suppliers')
            ->assertStatus(200)
            ->assertJsonMissingPath('data.0.name');

        $this->postJson('/api/v1/suppliers/' . $supplier->id . '/reactivate')
            ->assertStatus(200)
            ->assertJsonPath('data.is_active', true);
    }

    public function test_deleted_supplier_is_not_returned_by_normal_queries(): void
    {
        $shop = Shop::factory()->create();
        $user = User::factory()->create();
        $user->shops()->attach($shop->id, ['role' => 'owner']);

        $supplier = Supplier::factory()->create([
            'shop_id' => $shop->id,
            'name' => 'Will Delete',
            'phone' => '+91 90000 00009',
        ]);

        $this->actingAs($user, 'sanctum');

        $this->deleteJson('/api/v1/suppliers/' . $supplier->id)->assertStatus(200);

        $this->getJson('/api/v1/suppliers')->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}
