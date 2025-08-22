<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tasks', function (Blueprint $t) {
            $t->longText('details')->nullable()->change();
        });
    }
    public function down(): void {
        Schema::table('tasks', function (Blueprint $t) {
            $t->text('details')->nullable()->change();
        });
    }
};
