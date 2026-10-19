<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('device_tokens', function(Blueprint $t){ $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->string('token',500)->unique(); $t->string('platform',30)->nullable(); $t->string('device_id',150)->nullable(); $t->timestamp('last_seen_at')->nullable(); $t->timestamps(); $t->index(['user_id','platform']); }); }
 public function down(): void { Schema::dropIfExists('device_tokens'); }
};
