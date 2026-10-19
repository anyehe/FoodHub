<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('role', 40)->nullable();
            $t->string('method', 10);
            $t->string('route', 255);
            $t->string('action', 255)->nullable();
            $t->unsignedSmallInteger('status_code')->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->string('user_agent', 500)->nullable();
            $t->json('payload')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'created_at']);
            $t->index(['route', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
