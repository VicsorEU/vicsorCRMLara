<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tasks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('board_id')->constrained('task_boards')->cascadeOnDelete();
            $t->foreignId('column_id')->constrained('task_columns')->cascadeOnDelete();
            $t->string('title');
            $t->text('details')->nullable();
            $t->date('due_at')->nullable();
            $t->enum('priority', ['low','normal','high','p1','p2'])->default('normal');
            $t->enum('type', ['in','out','transfer','adjust','common'])->default('common');
            $t->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $t->unsignedInteger('card_order')->default(0);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('tasks'); }
};
