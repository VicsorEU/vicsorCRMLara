<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('warehouses', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('code')->unique();              // короткий код/слаг склада
            $t->text('description')->nullable();
            $t->foreignId('parent_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $t->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('phone', 50)->nullable();

            // адрес (простая схема)
            $t->string('country')->nullable();
            $t->string('region')->nullable();
            $t->string('city')->nullable();
            $t->string('street')->nullable();
            $t->string('house')->nullable();
            $t->string('postal_code', 20)->nullable();

            $t->boolean('is_active')->default(true);
            $t->boolean('allow_negative_stock')->default(false);
            $t->integer('sort_order')->default(0);

            $t->timestamps();
            $t->index(['parent_id','sort_order']);
            $t->index('name');
        });
    }
    public function down(): void { Schema::dropIfExists('warehouses'); }
};
