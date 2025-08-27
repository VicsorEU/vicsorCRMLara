<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'access_role_id')) {
                $table->foreignId('access_role_id')
                    ->nullable()
                    ->constrained('access_roles')
                    ->nullOnDelete()
                    ->after('company');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'access_role_id')) {
                $table->dropConstrainedForeignId('access_role_id');
            }
        });
    }
};
