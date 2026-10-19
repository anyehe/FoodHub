<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('product_options',function(Blueprint $t){$t->id();$t->foreignId('product_id')->constrained()->cascadeOnDelete();$t->string('name');$t->decimal('price',12,2)->default(0);$t->boolean('is_required')->default(false);$t->unsignedInteger('max_choices')->default(1);$t->timestamps();}); } public function down(): void { Schema::dropIfExists('product_options'); } };
