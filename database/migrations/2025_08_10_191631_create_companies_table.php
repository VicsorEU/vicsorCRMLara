<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('companies', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->nullable();
            $t->string('phone', 32)->nullable();
            $t->string('website')->nullable();
            $t->string('tax_number')->nullable();
            $t->string('city')->nullable();
            $t->string('country')->nullable();
            $t->string('address')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();

            $t->index(['name']);
            $t->index(['owner_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('companies'); }
};
