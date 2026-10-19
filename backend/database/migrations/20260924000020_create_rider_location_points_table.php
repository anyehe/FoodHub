<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('rider_location_points', function(Blueprint $t){ $t->id(); $t->foreignId('delivery_assignment_id')->constrained()->cascadeOnDelete(); $t->foreignId('rider_id')->constrained('users')->cascadeOnDelete(); $t->decimal('latitude',10,7); $t->decimal('longitude',10,7); $t->timestamp('recorded_at')->useCurrent(); $t->index(['delivery_assignment_id','recorded_at']); }); }
 public function down(): void { Schema::dropIfExists('rider_location_points'); }
};
