<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::view('/dashboard', 'dashboard-new')->name('dashboard');
Route::view('/products', 'products.index-new')->name('products.index');
Route::view('/suppliers', 'suppliers.index-new')->name('suppliers.index');
Route::view('/purchase-orders', 'purchase-orders.index-new')->name('purchase-orders.index');
Route::view('/purchase-orders/create', 'purchase-orders.create')->name('purchase-orders.create');
Route::get('/purchase-orders/{purchaseOrder}', function ($purchaseOrder) {
    return view('purchase-orders.show', ['purchaseOrderId' => $purchaseOrder]);
})->whereNumber('purchaseOrder')->name('purchase-orders.show');
Route::view('/suppliers/create', 'suppliers.create')->name('suppliers.create');
Route::get('/suppliers/{supplier}', function ($supplier) {
    return view('suppliers.show', ['supplierId' => $supplier]);
})->name('suppliers.show');
Route::get('/suppliers/{supplier}/edit', function ($supplier) {
    return view('suppliers.edit', ['supplierId' => $supplier]);
})->name('suppliers.edit');
