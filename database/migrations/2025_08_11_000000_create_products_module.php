<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->boolean('is_variable')->default(false);
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('sku')->nullable()->unique();
            $t->string('barcode')->nullable();
            $t->decimal('price_regular', 12, 2)->default(0);
            $t->decimal('price_sale', 12, 2)->nullable();
            $t->decimal('weight', 12, 3)->nullable();
            $t->decimal('length', 12, 3)->nullable();
            $t->decimal('width', 12, 3)->nullable();
            $t->decimal('height', 12, 3)->nullable();
            $t->text('short_description')->nullable();
            $t->longText('description')->nullable();
            $t->timestamps();
            $t->index('name');
        });

        // Должна быть раньше images, т.к. images имеет FK на variation_id
        Schema::create('product_variations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->string('sku')->nullable()->unique();
            $t->string('barcode')->nullable();
            $t->decimal('price_regular', 12, 2)->default(0);
            $t->decimal('price_sale', 12, 2)->nullable();
            $t->decimal('weight', 12, 3)->nullable();
            $t->decimal('length', 12, 3)->nullable();
            $t->decimal('width', 12, 3)->nullable();
            $t->decimal('height', 12, 3)->nullable();
            $t->text('description')->nullable();
            $t->timestamps();
        });

        Schema::create('product_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $t->foreignId('variation_id')->nullable()->constrained('product_variations')->cascadeOnDelete();
            $t->string('path');
            $t->boolean('is_primary')->default(false);
            $t->integer('sort_order')->default(0);
            $t->string('session_token', 100)->nullable();
            $t->timestamps();
            $t->index(['product_id','sort_order']);
            $t->index(['variation_id']);
        });

        Schema::create('product_attribute_value', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->foreignId('attribute_value_id')->constrained('attribute_values')->cascadeOnDelete();
            $t->unique(['product_id','attribute_value_id']);
        });

        Schema::create('variation_attribute_value', function (Blueprint $t) {
            $t->id();
            $t->foreignId('variation_id')->constrained('product_variations')->cascadeOnDelete();
            $t->foreignId('attribute_value_id')->constrained('attribute_values')->cascadeOnDelete();
            $t->unique(['variation_id','attribute_value_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variation_attribute_value');
        Schema::dropIfExists('product_attribute_value');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_variations');
        Schema::dropIfExists('products');
    }

};
