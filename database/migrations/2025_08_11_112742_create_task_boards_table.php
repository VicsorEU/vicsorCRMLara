<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('task_boards', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('task_boards'); }
};
