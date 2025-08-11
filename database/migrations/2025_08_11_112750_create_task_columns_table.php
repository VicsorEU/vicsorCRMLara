<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('task_columns', function (Blueprint $t) {
            $t->id();
            $t->foreignId('board_id')->constrained('task_boards')->cascadeOnDelete();
            $t->string('name');
            $t->string('color', 16)->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('task_columns'); }
};
