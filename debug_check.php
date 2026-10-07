<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$html = view('products.index')->render();
var_dump(strpos($html, 'name="product_name"') !== false);
var_dump(strpos($html, 'name="selling_price"') !== false);
var_dump(strpos($html, 'name="current_quantity"') !== false);
