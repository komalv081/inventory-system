<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]
        );

        $shop = Shop::query()->where('is_active', true)->first();

        if (! $shop) {
            $shop = Shop::create([
                'name' => 'Test Shop',
                'currency' => 'INR',
                'timezone' => 'Asia/Kolkata',
                'country' => 'IN',
                'is_active' => true,
            ]);
        }

        $shop->users()->syncWithoutDetaching([
            $user->id => ['role' => 'owner'],
        ]);

        $this->call(ProductSeeder::class);
    }
}
