<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->json('clauses');
            $table->text('changes_summary')->nullable();
            $table->string('hash', 64)->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('frozen_at')->nullable();
            $table->timestamps();

            $table->unique(['contract_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_versions');
    }
};
