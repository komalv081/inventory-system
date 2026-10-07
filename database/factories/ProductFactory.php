<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'barcode' => $this->faker->ean13(),
            'sku' => 'SKU-' . $this->faker->unique()->randomNumber(6),
            'product_name' => $this->faker->words(3, true),
            'brand' => $this->faker->company(),
            'category' => 'General',
            'subcategory' => 'Retail',
            'description' => $this->faker->sentence(),
            'pack_size' => '500g',
            'unit' => 'pcs',
            'mrp' => $this->faker->randomFloat(2, 10, 500),
            'selling_price' => $this->faker->randomFloat(2, 10, 500),
            'purchase_price' => $this->faker->randomFloat(2, 10, 400),
            'tax_rate' => 5.0,
            'current_quantity' => 25,
            'minimum_stock_threshold' => 10,
            'product_image' => null,
            'is_active' => true,
        ];
    }
}
