<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('customer_channels', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            // telegram | viber | whatsapp | instagram | facebook
            $t->string('kind', 20);
            $t->string('value', 191); // @username, номер, url и т.п.
            $t->timestamps();

            $t->index(['customer_id', 'kind']);
        });
    }
    public function down(): void { Schema::dropIfExists('customer_channels'); }
};
