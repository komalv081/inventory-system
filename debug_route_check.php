<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$response = $app->handle(Illuminate\Http\Request::create('/products', 'GET'));
var_dump($response->status());
var_dump(strpos($response->getContent(), 'name="product_name"') !== false);
var_dump(strpos($response->getContent(), 'name="selling_price"') !== false);
var_dump(strpos($response->getContent(), 'name="current_quantity"') !== false);
