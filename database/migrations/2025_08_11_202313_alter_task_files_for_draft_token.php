<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('task_files', function (Blueprint $table) {
            // если этих колонок ещё нет — добавим
            if (!Schema::hasColumn('task_files', 'user_id')) {
                $table->foreignId('user_id')->after('id')->constrained()->cascadeOnDelete();
            }
            if (!Schema::hasColumn('task_files', 'draft_token')) {
                $table->string('draft_token', 100)->nullable()->index()->after('task_id');
            }
            if (!Schema::hasColumn('task_files', 'mime')) {
                $table->string('mime', 191)->nullable()->after('path');
            }

            // позволяем хранить файлы до того, как задача создана
            try { $table->unsignedBigInteger('size')->nullable()->change(); } catch (\Throwable $e) {}
            try { $table->foreignId('task_id')->nullable()->change(); } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('task_files', function (Blueprint $table) {
            if (Schema::hasColumn('task_files', 'user_id'))   $table->dropConstrainedForeignId('user_id');
            if (Schema::hasColumn('task_files', 'draft_token')) $table->dropColumn('draft_token');
            if (Schema::hasColumn('task_files', 'mime'))        $table->dropColumn('mime');

            try { $table->unsignedBigInteger('size')->nullable(false)->change(); } catch (\Throwable $e) {}
            try { $table->foreignId('task_id')->nullable(false)->change(); } catch (\Throwable $e) {}
        });
    }
};
