<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attributes', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();          // латиница, уникально
            $t->text('description')->nullable();   // на будущее (если понадобится)
            $t->foreignId('parent_id')->nullable()->constrained('attributes')->nullOnDelete();
            $t->timestamps();
            $t->index('name');
        });

        Schema::create('attribute_values', function (Blueprint $t) {
            $t->id();
            $t->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $t->string('name');                     // «Значение»
            $t->string('slug');                     // «Слаг»
            $t->integer('sort_order')->default(0);  // «Порядок»
            $t->timestamps();
            $t->unique(['attribute_id','slug']);    // слаг уникален внутри атрибута
            $t->index(['attribute_id','sort_order']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
    }
};
