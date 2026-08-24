<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('contract_type');
            $table->string('transaction_type')->nullable();
            $table->string('jurisdiction')->nullable();
            $table->unsignedInteger('order');
            $table->string('key');
            $table->string('title');
            $table->text('purpose');                    // por qué se necesita (lenguaje claro)
            $table->text('steps')->nullable();          // cómo conseguirlo, paso a paso
            $table->text('legal_note')->nullable();
            $table->string('link_label')->nullable();
            $table->string('link_url')->nullable();
            $table->boolean('mandatory')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_requirements');
    }
};
