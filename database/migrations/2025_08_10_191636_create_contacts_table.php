<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('contacts', function (Blueprint $t) {
            $t->id();
            $t->string('first_name');
            $t->string('last_name')->nullable();
            $t->string('email')->nullable();
            $t->string('phone', 32)->nullable();
            $t->string('position')->nullable();
            $t->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $t->text('notes')->nullable();
            $t->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();

            $t->index(['first_name','last_name']);
            $t->index(['company_id']);
            $t->index(['owner_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('contacts'); }
};
