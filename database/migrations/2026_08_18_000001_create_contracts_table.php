<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('contract_type');       // bienes_muebles | inmuebles | vehiculos | servicios | internacional
            $table->string('transaction_type');    // b2b | b2c | c2c | c2b
            $table->string('jurisdiction');        // nacional | intracomunitario | internacional
            $table->string('title');
            $table->string('object_type')->nullable();
            $table->text('object_description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price_amount', 14, 2);
            $table->string('currency', 3)->default('EUR');
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2);
            $table->string('city');
            $table->date('signing_date');
            $table->date('effective_date')->nullable();
            $table->text('delivery_terms')->nullable();
            $table->text('payment_terms')->nullable();
            $table->text('warranties')->nullable();
            $table->text('special_clauses')->nullable();
            $table->json('clauses')->nullable();
            $table->string('status')->default('borrador'); // borrador | valido | firmado | cancelado
            $table->string('language', 5)->default('es');
            $table->text('legal_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
