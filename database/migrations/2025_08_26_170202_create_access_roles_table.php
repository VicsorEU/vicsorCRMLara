<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('access_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('abilities')->nullable(); // в Postgres будет jsonb
            $table->boolean('system')->default(false);
            $table->timestamps();
        });

        // seed трёх стандартных ролей
        $now = now();
        DB::table('access_roles')->insert([
            [
                'name'      => 'Администратор',
                'slug'      => 'admin',
                'abilities' => json_encode([
                    'settings_edit' => true,
                    'projects'      => 'full', // full|read|own|none
                ], JSON_UNESCAPED_UNICODE),
                'system'    => true,
                'created_at'=> $now, 'updated_at'=> $now,
            ],
            [
                'name'      => 'Менеджер',
                'slug'      => 'manager',
                'abilities' => json_encode([
                    'settings_edit' => false,
                    'projects'      => 'read',
                ], JSON_UNESCAPED_UNICODE),
                'system'    => true,
                'created_at'=> $now, 'updated_at'=> $now,
            ],
            [
                'name'      => 'Продавец',
                'slug'      => 'seller',
                'abilities' => json_encode([
                    'settings_edit' => false,
                    'projects'      => 'own',
                ], JSON_UNESCAPED_UNICODE),
                'system'    => true,
                'created_at'=> $now, 'updated_at'=> $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('access_roles');
    }
};
