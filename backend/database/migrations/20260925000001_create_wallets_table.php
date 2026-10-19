<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('wallets', function(Blueprint $t){$t->id();$t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();$t->decimal('balance',12,2)->default(0);$t->string('currency',3)->default('PHP');$t->timestamps();}); } public function down(): void { Schema::dropIfExists('wallets'); } };
