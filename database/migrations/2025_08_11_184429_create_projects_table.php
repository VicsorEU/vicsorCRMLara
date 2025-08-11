<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('projects', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete(); // ответственный
            $t->date('start_date')->nullable();
            $t->text('note')->nullable();
            $t->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('projects'); }
};
