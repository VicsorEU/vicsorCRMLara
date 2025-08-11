<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('customer_addresses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $t->string('label', 60)->default('Основной'); // подпись адреса
            $t->string('country')->nullable();
            $t->string('region')->nullable();
            $t->string('city')->nullable();
            $t->string('street')->nullable();
            $t->string('house')->nullable();
            $t->string('apartment')->nullable();
            $t->string('postal_code', 20)->nullable();
            $t->boolean('is_default')->default(true);
            $t->timestamps();

            $t->index(['customer_id','is_default']);
        });
    }
    public function down(): void { Schema::dropIfExists('customer_addresses'); }
};
