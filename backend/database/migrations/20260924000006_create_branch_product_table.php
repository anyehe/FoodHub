<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('branch_product',function(Blueprint $t){$t->foreignId('branch_id')->constrained()->cascadeOnDelete();$t->foreignId('product_id')->constrained()->cascadeOnDelete();$t->decimal('price',12,2)->nullable();$t->boolean('is_available')->default(true);$t->primary(['branch_id','product_id']);}); } public function down(): void { Schema::dropIfExists('branch_product'); } };
