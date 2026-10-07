<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed 100 test products for the default shop.
     * Price values are stored in Indian Rupees (INR).
     */
    public function run(): void
    {
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

        $products = [
            ['name' => 'Aashri Tea', 'brand' => 'Aashri', 'category' => 'Beverages', 'pack_size' => '500g', 'unit' => 'pcs', 'mrp' => 120.00, 'selling_price' => 110.00, 'purchase_price' => 90.00, 'quantity' => 42],
            ['name' => 'Alphonso Mango', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 850.00, 'selling_price' => 800.00, 'purchase_price' => 680.00, 'quantity' => 28],
            ['name' => 'Amritsari Fish', 'brand' => 'Coastal Foods', 'category' => 'Protein', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 1050.00, 'selling_price' => 990.00, 'purchase_price' => 850.00, 'quantity' => 18],
            ['name' => 'Annie Cream Biscuit', 'brand' => 'Parle', 'category' => 'Bakery', 'pack_size' => '100g', 'unit' => 'pcs', 'mrp' => 45.00, 'selling_price' => 42.00, 'purchase_price' => 30.00, 'quantity' => 75],
            ['name' => 'Asha Milk Powder', 'brand' => 'Asha', 'category' => 'Dairy', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 680.00, 'selling_price' => 640.00, 'purchase_price' => 540.00, 'quantity' => 32],
            ['name' => 'Basmati Rice', 'brand' => 'Royal', 'category' => 'Grains', 'pack_size' => '5 kg', 'unit' => 'kg', 'mrp' => 1050.00, 'selling_price' => 990.00, 'purchase_price' => 820.00, 'quantity' => 22],
            ['name' => 'Bell Pepper', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 300.00, 'selling_price' => 280.00, 'purchase_price' => 220.00, 'quantity' => 55],
            ['name' => 'Bengali Mustard Oil', 'brand' => 'Shree', 'category' => 'Oils', 'pack_size' => '1 litre', 'unit' => 'litres', 'mrp' => 430.00, 'selling_price' => 395.00, 'purchase_price' => 330.00, 'quantity' => 36],
            ['name' => 'Beverages Cola', 'brand' => 'Coca-Cola', 'category' => 'Beverages', 'pack_size' => '1 litre', 'unit' => 'litres', 'mrp' => 120.00, 'selling_price' => 110.00, 'purchase_price' => 80.00, 'quantity' => 64],
            ['name' => 'Biryani Masala', 'brand' => 'Kashmiri', 'category' => 'Spices', 'pack_size' => '200g', 'unit' => 'pcs', 'mrp' => 160.00, 'selling_price' => 145.00, 'purchase_price' => 105.00, 'quantity' => 48],
            ['name' => 'Black Pepper', 'brand' => 'Nature Spice', 'category' => 'Spices', 'pack_size' => '200g', 'unit' => 'pcs', 'mrp' => 280.00, 'selling_price' => 250.00, 'purchase_price' => 190.00, 'quantity' => 40],
            ['name' => 'Brinjal', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 240.00, 'selling_price' => 220.00, 'purchase_price' => 170.00, 'quantity' => 60],
            ['name' => 'Broken Wheat', 'brand' => 'Shree', 'category' => 'Grains', 'pack_size' => '5 kg', 'unit' => 'kg', 'mrp' => 620.00, 'selling_price' => 580.00, 'purchase_price' => 490.00, 'quantity' => 25],
            ['name' => 'Brown Bread', 'brand' => 'Fresh Bakery', 'category' => 'Bakery', 'pack_size' => '1 loaf', 'unit' => 'pcs', 'mrp' => 70.00, 'selling_price' => 65.00, 'purchase_price' => 45.00, 'quantity' => 50],
            ['name' => 'Butter', 'brand' => 'Amrit', 'category' => 'Dairy', 'pack_size' => '200g', 'unit' => 'pcs', 'mrp' => 220.00, 'selling_price' => 205.00, 'purchase_price' => 170.00, 'quantity' => 30],
            ['name' => 'Cabbage', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 150.00, 'selling_price' => 140.00, 'purchase_price' => 100.00, 'quantity' => 65],
            ['name' => 'Chana Dal', 'brand' => 'Shree', 'category' => 'Pulses', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 180.00, 'selling_price' => 165.00, 'purchase_price' => 135.00, 'quantity' => 45],
            ['name' => 'Chilli Powder', 'brand' => 'Nature Spice', 'category' => 'Spices', 'pack_size' => '200g', 'unit' => 'pcs', 'mrp' => 220.00, 'selling_price' => 200.00, 'purchase_price' => 155.00, 'quantity' => 44],
            ['name' => 'Chicken', 'brand' => 'Farm Fresh', 'category' => 'Proteins', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 1050.00, 'selling_price' => 990.00, 'purchase_price' => 820.00, 'quantity' => 22],
            ['name' => 'Coconut Oil', 'brand' => 'Pure Roots', 'category' => 'Oils', 'pack_size' => '1 litre', 'unit' => 'litres', 'mrp' => 700.00, 'selling_price' => 640.00, 'purchase_price' => 510.00, 'quantity' => 18],
            ['name' => 'Corn Flakes', 'brand' => 'Kellogg', 'category' => 'Breakfast', 'pack_size' => '500g', 'unit' => 'pcs', 'mrp' => 410.00, 'selling_price' => 380.00, 'purchase_price' => 300.00, 'quantity' => 34],
            ['name' => 'Cotton Seed Oil', 'brand' => 'Shree', 'category' => 'Oils', 'pack_size' => '1 litre', 'unit' => 'litres', 'mrp' => 460.00, 'selling_price' => 420.00, 'purchase_price' => 350.00, 'quantity' => 30],
            ['name' => 'Cucumber', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 110.00, 'selling_price' => 100.00, 'purchase_price' => 70.00, 'quantity' => 70],
            ['name' => 'Curd', 'brand' => 'Amrit', 'category' => 'Dairy', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 180.00, 'selling_price' => 165.00, 'purchase_price' => 130.00, 'quantity' => 50],
            ['name' => 'Dry Beans', 'brand' => 'Nature Food', 'category' => 'Pulses', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 420.00, 'selling_price' => 390.00, 'purchase_price' => 315.00, 'quantity' => 28],
            ['name' => 'Eggs', 'brand' => 'Farm Fresh', 'category' => 'Proteins', 'pack_size' => '12 pcs', 'unit' => 'pcs', 'mrp' => 160.00, 'selling_price' => 150.00, 'purchase_price' => 115.00, 'quantity' => 55],
            ['name' => 'Elaichi', 'brand' => 'Nature Spice', 'category' => 'Spices', 'pack_size' => '100g', 'unit' => 'pcs', 'mrp' => 450.00, 'selling_price' => 410.00, 'purchase_price' => 310.00, 'quantity' => 25],
            ['name' => 'Flour', 'brand' => 'Shree', 'category' => 'Grains', 'pack_size' => '5 kg', 'unit' => 'kg', 'mrp' => 760.00, 'selling_price' => 700.00, 'purchase_price' => 590.00, 'quantity' => 20],
            ['name' => 'Fresh Cream', 'brand' => 'Amrit', 'category' => 'Dairy', 'pack_size' => '500g', 'unit' => 'pcs', 'mrp' => 320.00, 'selling_price' => 295.00, 'purchase_price' => 230.00, 'quantity' => 35],
            ['name' => 'Ghee', 'brand' => 'Amrit', 'category' => 'Dairy', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 2200.00, 'selling_price' => 2050.00, 'purchase_price' => 1750.00, 'quantity' => 12],
            ['name' => 'Ginger', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '500g', 'unit' => 'pcs', 'mrp' => 130.00, 'selling_price' => 120.00, 'purchase_price' => 85.00, 'quantity' => 62],
            ['name' => 'Granola', 'brand' => 'Nature Foods', 'category' => 'Breakfast', 'pack_size' => '500g', 'unit' => 'pcs', 'mrp' => 520.00, 'selling_price' => 480.00, 'purchase_price' => 390.00, 'quantity' => 24],
            ['name' => 'Green Tea', 'brand' => 'Aashri', 'category' => 'Beverages', 'pack_size' => '100g', 'unit' => 'pcs', 'mrp' => 360.00, 'selling_price' => 330.00, 'purchase_price' => 250.00, 'quantity' => 38],
            ['name' => 'Ground Turmeric', 'brand' => 'Nature Spice', 'category' => 'Spices', 'pack_size' => '200g', 'unit' => 'pcs', 'mrp' => 180.00, 'selling_price' => 165.00, 'purchase_price' => 125.00, 'quantity' => 46],
            ['name' => 'Guava', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 700.00, 'selling_price' => 650.00, 'purchase_price' => 510.00, 'quantity' => 26],
            ['name' => 'Health Drinks', 'brand' => 'Nutrition Plus', 'category' => 'Beverages', 'pack_size' => '1 litre', 'unit' => 'litres', 'mrp' => 250.00, 'selling_price' => 225.00, 'purchase_price' => 160.00, 'quantity' => 48],
            ['name' => 'Honey', 'brand' => 'Pure Roots', 'category' => 'Pantry', 'pack_size' => '500g', 'unit' => 'pcs', 'mrp' => 650.00, 'selling_price' => 600.00, 'purchase_price' => 490.00, 'quantity' => 20],
            ['name' => 'Hot Chocolate', 'brand' => 'Cocoa Co', 'category' => 'Beverages', 'pack_size' => '500g', 'unit' => 'pcs', 'mrp' => 620.00, 'selling_price' => 570.00, 'purchase_price' => 450.00, 'quantity' => 20],
            ['name' => 'Ice Cream', 'brand' => 'Cream House', 'category' => 'Frozen Food', 'pack_size' => '1 litre', 'unit' => 'litres', 'mrp' => 900.00, 'selling_price' => 840.00, 'purchase_price' => 680.00, 'quantity' => 15],
            ['name' => 'Indian Bread', 'brand' => 'Fresh Bakery', 'category' => 'Bakery', 'pack_size' => '1 loaf', 'unit' => 'pcs', 'mrp' => 85.00, 'selling_price' => 78.00, 'purchase_price' => 55.00, 'quantity' => 48],
            ['name' => 'Instrumental Oil', 'brand' => 'Shree', 'category' => 'Oils', 'pack_size' => '1 litre', 'unit' => 'litres', 'mrp' => 400.00, 'selling_price' => 370.00, 'purchase_price' => 300.00, 'quantity' => 31],
            ['name' => 'Jaggery', 'brand' => 'Nature Sweet', 'category' => 'Pantry', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 320.00, 'selling_price' => 295.00, 'purchase_price' => 220.00, 'quantity' => 42],
            ['name' => 'Jam', 'brand' => 'Fruit Good', 'category' => 'Pantry', 'pack_size' => '500g', 'unit' => 'pcs', 'mrp' => 280.00, 'selling_price' => 255.00, 'purchase_price' => 190.00, 'quantity' => 38],
            ['name' => 'Jeera', 'brand' => 'Nature Spice', 'category' => 'Spices', 'pack_size' => '100g', 'unit' => 'pcs', 'mrp' => 180.00, 'selling_price' => 165.00, 'purchase_price' => 115.00, 'quantity' => 42],
            ['name' => 'Juice', 'brand' => 'Fresh Press', 'category' => 'Beverages', 'pack_size' => '1 litre', 'unit' => 'litres', 'mrp' => 300.00, 'selling_price' => 275.00, 'purchase_price' => 200.00, 'quantity' => 40],
            ['name' => 'Kale Chips', 'brand' => 'Green Foods', 'category' => 'Snacks', 'pack_size' => '200g', 'unit' => 'pcs', 'mrp' => 240.00, 'selling_price' => 220.00, 'purchase_price' => 160.00, 'quantity' => 42],
            ['name' => 'Kheer', 'brand' => 'Tasty', 'category' => 'Desserts', 'pack_size' => '500g', 'unit' => 'pcs', 'mrp' => 360.00, 'selling_price' => 330.00, 'purchase_price' => 240.00, 'quantity' => 30],
            ['name' => 'Lemon', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 180.00, 'selling_price' => 165.00, 'purchase_price' => 115.00, 'quantity' => 60],
            ['name' => 'Lentils', 'brand' => 'Shree', 'category' => 'Pulses', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 230.00, 'selling_price' => 210.00, 'purchase_price' => 170.00, 'quantity' => 48],
            ['name' => 'Mango Pickle', 'brand' => 'Asha', 'category' => 'Pantry', 'pack_size' => '500g', 'unit' => 'pcs', 'mrp' => 310.00, 'selling_price' => 285.00, 'purchase_price' => 210.00, 'quantity' => 35],
            ['name' => 'Mangoes', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 900.00, 'selling_price' => 840.00, 'purchase_price' => 700.00, 'quantity' => 24],
            ['name' => 'Masala Chai', 'brand' => 'Aashri', 'category' => 'Beverages', 'pack_size' => '100g', 'unit' => 'pcs', 'mrp' => 160.00, 'selling_price' => 145.00, 'purchase_price' => 105.00, 'quantity' => 52],
            ['name' => 'Meat', 'brand' => 'Farm Fresh', 'category' => 'Proteins', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 1500.00, 'selling_price' => 1390.00, 'purchase_price' => 1100.00, 'quantity' => 16],
            ['name' => 'Milk', 'brand' => 'Amrit', 'category' => 'Dairy', 'pack_size' => '1 litre', 'unit' => 'litres', 'mrp' => 90.00, 'selling_price' => 82.00, 'purchase_price' => 64.00, 'quantity' => 60],
            ['name' => 'Munger Dal', 'brand' => 'Shree', 'category' => 'Pulses', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 190.00, 'selling_price' => 175.00, 'purchase_price' => 135.00, 'quantity' => 44],
            ['name' => 'Noodles', 'brand' => 'Bite', 'category' => 'Snacks', 'pack_size' => '500g', 'unit' => 'pcs', 'mrp' => 230.00, 'selling_price' => 210.00, 'purchase_price' => 145.00, 'quantity' => 45],
            ['name' => 'Onion', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 140.00, 'selling_price' => 130.00, 'purchase_price' => 90.00, 'quantity' => 70],
            ['name' => 'Orej Cookies', 'brand' => 'Parle', 'category' => 'Bakery', 'pack_size' => '100g', 'unit' => 'pcs', 'mrp' => 60.00, 'selling_price' => 55.00, 'purchase_price' => 38.00, 'quantity' => 72],
            ['name' => 'Pasta', 'brand' => 'Italy Foods', 'category' => 'Snacks', 'pack_size' => '500g', 'unit' => 'pcs', 'mrp' => 330.00, 'selling_price' => 300.00, 'purchase_price' => 220.00, 'quantity' => 36],
            ['name' => 'Peanut Butter', 'brand' => 'Nature Food', 'category' => 'Pantry', 'pack_size' => '500g', 'unit' => 'pcs', 'mrp' => 780.00, 'selling_price' => 720.00, 'purchase_price' => 560.00, 'quantity' => 18],
            ['name' => 'Pineapple', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 800.00, 'selling_price' => 740.00, 'purchase_price' => 610.00, 'quantity' => 25],
            ['name' => 'Potato', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 120.00, 'selling_price' => 110.00, 'purchase_price' => 75.00, 'quantity' => 68],
            ['name' => 'Prawn', 'brand' => 'Coastal Foods', 'category' => 'Proteins', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 2200.00, 'selling_price' => 2050.00, 'purchase_price' => 1650.00, 'quantity' => 10],
            ['name' => 'Pudding Powder', 'brand' => 'Tasty', 'category' => 'Desserts', 'pack_size' => '500g', 'unit' => 'pcs', 'mrp' => 580.00, 'selling_price' => 530.00, 'purchase_price' => 400.00, 'quantity' => 21],
            ['name' => 'Red Chilli', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 260.00, 'selling_price' => 240.00, 'purchase_price' => 180.00, 'quantity' => 52],
            ['name' => 'Rice Cake', 'brand' => 'Nature Foods', 'category' => 'Snacks', 'pack_size' => '200g', 'unit' => 'pcs', 'mrp' => 240.00, 'selling_price' => 220.00, 'purchase_price' => 160.00, 'quantity' => 40],
            ['name' => 'Roti', 'brand' => 'Fresh Bakery', 'category' => 'Bakery', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 140.00, 'selling_price' => 130.00, 'purchase_price' => 95.00, 'quantity' => 52],
            ['name' => 'Salmon Fish', 'brand' => 'Coastal Foods', 'category' => 'Proteins', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 2400.00, 'selling_price' => 2200.00, 'purchase_price' => 1850.00, 'quantity' => 10],
            ['name' => 'Salsa', 'brand' => 'Fresh Foods', 'category' => 'Pantry', 'pack_size' => '500g', 'unit' => 'pcs', 'mrp' => 300.00, 'selling_price' => 275.00, 'purchase_price' => 200.00, 'quantity' => 38],
            ['name' => 'Samosa', 'brand' => 'Fresh Bakery', 'category' => 'Bakery', 'pack_size' => '1 piece', 'unit' => 'pcs', 'mrp' => 95.00, 'selling_price' => 85.00, 'purchase_price' => 55.00, 'quantity' => 48],
            ['name' => 'Sardine Fish', 'brand' => 'Coastal Foods', 'category' => 'Proteins', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 1400.00, 'selling_price' => 1280.00, 'purchase_price' => 1010.00, 'quantity' => 14],
            ['name' => 'Soya Chunks', 'brand' => 'Nature Food', 'category' => 'Proteins', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 600.00, 'selling_price' => 550.00, 'purchase_price' => 420.00, 'quantity' => 22],
            ['name' => 'Spinach', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 200.00, 'selling_price' => 185.00, 'purchase_price' => 130.00, 'quantity' => 56],
            ['name' => 'Strawberry', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 1400.00, 'selling_price' => 1290.00, 'purchase_price' => 1010.00, 'quantity' => 14],
            ['name' => 'Sugar', 'brand' => 'Shree', 'category' => 'Pantry', 'pack_size' => '5 kg', 'unit' => 'kg', 'mrp' => 760.00, 'selling_price' => 700.00, 'purchase_price' => 570.00, 'quantity' => 22],
            ['name' => 'Sunflower Oil', 'brand' => 'Shree', 'category' => 'Oils', 'pack_size' => '1 litre', 'unit' => 'litres', 'mrp' => 520.00, 'selling_price' => 480.00, 'purchase_price' => 390.00, 'quantity' => 25],
            ['name' => 'Sweet Corn', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 300.00, 'selling_price' => 275.00, 'purchase_price' => 200.00, 'quantity' => 40],
            ['name' => 'Tamarind', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 300.00, 'selling_price' => 275.00, 'purchase_price' => 210.00, 'quantity' => 40],
            ['name' => 'Tandoori Chicken', 'brand' => 'Farm Fresh', 'category' => 'Proteins', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 1400.00, 'selling_price' => 1290.00, 'purchase_price' => 1000.00, 'quantity' => 15],
            ['name' => 'Tapioca', 'brand' => 'Nature Food', 'category' => 'Grains', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 450.00, 'selling_price' => 420.00, 'purchase_price' => 330.00, 'quantity' => 30],
            ['name' => 'Toast', 'brand' => 'Fresh Bakery', 'category' => 'Bakery', 'pack_size' => '1 loaf', 'unit' => 'pcs', 'mrp' => 110.00, 'selling_price' => 100.00, 'purchase_price' => 70.00, 'quantity' => 45],
            ['name' => 'Tomato', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 260.00, 'selling_price' => 240.00, 'purchase_price' => 180.00, 'quantity' => 52],
            ['name' => 'Tuna Fish', 'brand' => 'Coastal Foods', 'category' => 'Proteins', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 2000.00, 'selling_price' => 1850.00, 'purchase_price' => 1480.00, 'quantity' => 12],
            ['name' => 'Vegetable Soup', 'brand' => 'Fresh Foods', 'category' => 'Frozen Food', 'pack_size' => '500g', 'unit' => 'pcs', 'mrp' => 420.00, 'selling_price' => 390.00, 'purchase_price' => 290.00, 'quantity' => 28],
            ['name' => 'Wheat Flour', 'brand' => 'Shree', 'category' => 'Grains', 'pack_size' => '5 kg', 'unit' => 'kg', 'mrp' => 820.00, 'selling_price' => 760.00, 'purchase_price' => 620.00, 'quantity' => 20],
            ['name' => 'Yogurt', 'brand' => 'Amrit', 'category' => 'Dairy', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 210.00, 'selling_price' => 190.00, 'purchase_price' => 145.00, 'quantity' => 48],
            ['name' => 'Zucchini', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 250.00, 'selling_price' => 230.00, 'purchase_price' => 170.00, 'quantity' => 52],
            ['name' => 'Apple', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 330.00, 'selling_price' => 300.00, 'purchase_price' => 220.00, 'quantity' => 40],
            ['name' => 'Apricot', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 950.00, 'selling_price' => 880.00, 'purchase_price' => 720.00, 'quantity' => 18],
            ['name' => 'Avocado', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 1150.00, 'selling_price' => 1050.00, 'purchase_price' => 820.00, 'quantity' => 16],
            ['name' => 'Banana', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 85.00, 'selling_price' => 78.00, 'purchase_price' => 55.00, 'quantity' => 72],
            ['name' => 'Chickpeas', 'brand' => 'Shree', 'category' => 'Pulses', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 190.00, 'selling_price' => 175.00, 'purchase_price' => 140.00, 'quantity' => 44],
            ['name' => 'Cinnamon', 'brand' => 'Nature Spice', 'category' => 'Spices', 'pack_size' => '100g', 'unit' => 'pcs', 'mrp' => 450.00, 'selling_price' => 410.00, 'purchase_price' => 320.00, 'quantity' => 25],
            ['name' => 'Coffee Beans', 'brand' => 'Roast House', 'category' => 'Beverages', 'pack_size' => '500g', 'unit' => 'pcs', 'mrp' => 1400.00, 'selling_price' => 1300.00, 'purchase_price' => 1000.00, 'quantity' => 14],
            ['name' => 'Dates', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 1050.00, 'selling_price' => 980.00, 'purchase_price' => 780.00, 'quantity' => 18],
            ['name' => 'Fig', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 1350.00, 'selling_price' => 1250.00, 'purchase_price' => 980.00, 'quantity' => 14],
            ['name' => 'Grapes', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 850.00, 'selling_price' => 790.00, 'purchase_price' => 620.00, 'quantity' => 22],
            ['name' => 'Pear', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 400.00, 'selling_price' => 370.00, 'purchase_price' => 290.00, 'quantity' => 32],
            ['name' => 'Plum', 'brand' => 'Fresh Farm', 'category' => 'Fresh Produce', 'pack_size' => '1 kg', 'unit' => 'kg', 'mrp' => 820.00, 'selling_price' => 760.00, 'purchase_price' => 600.00, 'quantity' => 24],
            ['name' => 'Sesame Oil', 'brand' => 'Pure Roots', 'category' => 'Oils', 'pack_size' => '1 litre', 'unit' => 'litres', 'mrp' => 680.00, 'selling_price' => 625.00, 'purchase_price' => 500.00, 'quantity' => 18],
        ];

        if (count($products) !== 100) {
            throw new \RuntimeException('Product seed data must contain exactly 100 products.');
        }

        $created = 0;
        foreach ($products as $index => $product) {
            $number = $index + 1;
            $sku = sprintf('PRD-%03d', $number);
            $barcode = sprintf('890123456789%03d', $number);

            Product::query()->where('shop_id', $shop->id)->where('sku', $sku)->firstOrCreate([
                'shop_id' => $shop->id,
                'sku' => $sku,
                'barcode' => $barcode,
                'product_name' => $product['name'],
                'brand' => $product['brand'],
                'category' => $product['category'],
                'subcategory' => 'Test Inventory',
                'description' => 'Test product seed data for inventory management.',
                'pack_size' => $product['pack_size'],
                'unit' => $product['unit'],
                'mrp' => $product['mrp'],
                'selling_price' => $product['selling_price'],
                'purchase_price' => $product['purchase_price'],
                'tax_rate' => 18.00,
                'current_quantity' => $product['quantity'],
                'minimum_stock_threshold' => 10,
                'is_active' => true,
            ]);

            $created++;
        }

        $this->command->info(sprintf('Seeded %d products in shop: %s', $created, $shop->name));
    }
}
