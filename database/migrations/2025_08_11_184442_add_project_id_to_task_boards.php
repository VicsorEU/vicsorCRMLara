<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('task_boards', function (Blueprint $t) {
            $t->foreignId('project_id')->after('id')->nullable()->constrained('projects')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::table('task_boards', function (Blueprint $t) {
            $t->dropConstrainedForeignId('project_id');
        });
    }
};
