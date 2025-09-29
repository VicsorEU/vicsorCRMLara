<?php

use App\Http\Controllers\AttributeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::group(['prefix' => 'shops'], function () {
        Route::get('/', [ShopController::class,'index'])->name('shops.index');
        Route::get('/create', [ShopController::class, 'create'])->name('shops.create');

        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('shops.product.edit');
        Route::get('attributes/{attribute}/edit', [AttributeController::class, 'edit'])->name('shops.attribute.edit');
        Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('shops.warehouse.edit');
        Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('shops.category.edit');
    });
});
