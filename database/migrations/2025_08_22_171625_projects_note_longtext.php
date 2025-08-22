<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('projects', function (Blueprint $t) {
            $t->longText('note')->nullable()->change();
        });
    }
    public function down(): void {
        Schema::table('projects', function (Blueprint $t) {
            $t->text('note')->nullable()->change();
        });
    }
};
