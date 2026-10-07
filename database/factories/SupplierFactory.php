<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'name' => $this->faker->company() . ' Imports',
            'company_name' => $this->faker->company(),
            'phone' => '+91 ' . $this->faker->numerify('##########'),
            'whatsapp_phone' => '+91 ' . $this->faker->numerify('##########'),
            'email' => $this->faker->safeEmail(),
            'address' => $this->faker->streetAddress(),
            'notes' => $this->faker->sentence(),
            'is_active' => true,
            'created_by' => null,
        ];
    }
}
