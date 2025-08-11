<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('customers', function (Blueprint $t) {
            $t->id();
            $t->string('full_name');
            $t->string('phone', 32)->nullable()->index();
            $t->string('email')->nullable()->index();
            $t->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $t->text('note')->nullable();
            $t->date('birth_date')->nullable();
            $t->string('country')->nullable(); // “Доп. поле: страна”
            $t->timestamps();
            $t->softDeletes();

            $t->index(['full_name']);
        });
    }
    public function down(): void { Schema::dropIfExists('customers'); }
};
