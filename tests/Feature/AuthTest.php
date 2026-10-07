<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_login(): void
    {
        $shop = Shop::factory()->create();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test Owner',
            'email' => 'owner@example.com',
            'password' => 'Password123!',
            'shop_name' => $shop->name,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('user.email', 'owner@example.com');

        $this->assertNotEmpty($response->json('token'));

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'owner@example.com',
            'password' => 'Password123!',
        ]);

        $login->assertStatus(200)
            ->assertJsonPath('user.email', 'owner@example.com');
    }

    public function test_unauthenticated_api_request_returns_401(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_browser_request_without_auth_does_not_crash_on_missing_login_route(): void
    {
        $response = $this->get('/api/v1/products');

        $response->assertRedirect('/login');
    }

    public function test_products_page_uses_backend_product_field_names(): void
    {
        $response = $this->get('/products');

        $response->assertOk()
            ->assertSee('name="product_name"')
            ->assertSee('name="selling_price"')
            ->assertSee('name="current_quantity"');
    }
}
