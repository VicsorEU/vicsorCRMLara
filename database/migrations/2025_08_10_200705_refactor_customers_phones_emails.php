<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Таблица телефонов
        Schema::create('customer_phones', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $t->string('value', 50);   // сам номер
            $t->string('label', 30)->nullable(); // метка типа "моб.", "раб."
            $t->timestamps();
            $t->index(['customer_id', 'value']);
        });

        // 2) Таблица e-mailов
        Schema::create('customer_emails', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $t->string('value'); // сам email
            $t->string('label', 30)->nullable();
            $t->timestamps();
            $t->index(['customer_id', 'value']);
        });

        // 3) Перенос существующих данных из customers.phone / customers.email
        if (Schema::hasColumn('customers', 'phone')) {
            $rows = DB::table('customers')->select('id', 'phone')->whereNotNull('phone')->get();
            foreach ($rows as $r) {
                DB::table('customer_phones')->insert([
                    'customer_id' => $r->id,
                    'value'       => $r->phone,
                    'label'       => null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
        if (Schema::hasColumn('customers', 'email')) {
            $rows = DB::table('customers')->select('id', 'email')->whereNotNull('email')->get();
            foreach ($rows as $r) {
                DB::table('customer_emails')->insert([
                    'customer_id' => $r->id,
                    'value'       => $r->email,
                    'label'       => null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        // 4) Удаляем столбцы phone, email, country у покупателей
        Schema::table('customers', function (Blueprint $t) {
            if (Schema::hasColumn('customers', 'phone'))   $t->dropColumn('phone');
            if (Schema::hasColumn('customers', 'email'))   $t->dropColumn('email');
            if (Schema::hasColumn('customers', 'country')) $t->dropColumn('country');
        });
    }

    public function down(): void
    {
        // Вернуть столбцы
        Schema::table('customers', function (Blueprint $t) {
            if (!Schema::hasColumn('customers', 'phone'))   $t->string('phone', 32)->nullable()->index();
            if (!Schema::hasColumn('customers', 'email'))   $t->string('email')->nullable()->index();
            if (!Schema::hasColumn('customers', 'country')) $t->string('country')->nullable();
        });

        // Простейший откат данных (берём по одному первому значению)
        $customers = DB::table('customers')->select('id')->get();
        foreach ($customers as $c) {
            $phone = DB::table('customer_phones')->where('customer_id', $c->id)->orderBy('id')->value('value');
            $email = DB::table('customer_emails')->where('customer_id', $c->id)->orderBy('id')->value('value');
            DB::table('customers')->where('id', $c->id)->update([
                'phone' => $phone,
                'email' => $email,
            ]);
        }

        Schema::dropIfExists('customer_phones');
        Schema::dropIfExists('customer_emails');
    }
};
