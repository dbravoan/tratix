<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_trail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event');
            $table->string('actor')->nullable();        // creator | seller | buyer | system
            $table->text('detail')->nullable();
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('happened_at');
            $table->timestamps();

            $table->index(['contract_id', 'happened_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trail');
    }
};
