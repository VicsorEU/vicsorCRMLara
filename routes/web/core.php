<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductMediaController;

Route::middleware('auth')->group(function () {
    // dashboard
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    // базовые CRUD
    Route::resource('companies', CompanyController::class);
    Route::resource('contacts', ContactController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('attributes', AttributeController::class)->except(['show']);
    Route::resource('warehouses', WarehouseController::class);
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    Route::get('/audit-ajax', [AuditController::class, 'indexAjax'])->name('audit.index_ajax');
    Route::resource('products', ProductController::class);

    // медиа для товаров
    Route::post('uploads/products', [ProductMediaController::class, 'upload'])->name('products.upload');
    Route::delete('uploads/products/{image}', [ProductMediaController::class, 'destroy'])
        ->whereNumber('image')->name('products.upload.delete');
});
