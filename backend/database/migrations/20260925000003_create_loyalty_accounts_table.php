<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('loyalty_accounts', function(Blueprint $t){$t->id();$t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();$t->unsignedBigInteger('points')->default(0);$t->string('tier',30)->default('bronze');$t->timestamps();}); } public function down(): void { Schema::dropIfExists('loyalty_accounts'); } };
