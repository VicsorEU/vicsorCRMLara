<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('task_files', function (Blueprint $t) {
            $t->id();
            $t->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $t->string('original_name');
            $t->string('path'); // storage path
            $t->unsignedBigInteger('size')->default(0);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('task_files'); }
};
