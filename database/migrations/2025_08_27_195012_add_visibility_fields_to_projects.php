// database/migrations/2025_08_27_000001_add_visibility_fields_to_projects.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('projects', function (Blueprint $t) {
            if (!Schema::hasColumn('projects','is_private'))   $t->boolean('is_private')->default(false)->after('manager_id');
            if (!Schema::hasColumn('projects','team_users'))   $t->json('team_users')->nullable()->after('is_private');   // jsonb в PG
            if (!Schema::hasColumn('projects','team_groups'))  $t->json('team_groups')->nullable()->after('team_users');
        });
    }
    public function down(): void {
        Schema::table('projects', function (Blueprint $t) {
            if (Schema::hasColumn('projects','team_groups')) $t->dropColumn('team_groups');
            if (Schema::hasColumn('projects','team_users'))  $t->dropColumn('team_users');
            if (Schema::hasColumn('projects','is_private'))  $t->dropColumn('is_private');
        });
    }
};
