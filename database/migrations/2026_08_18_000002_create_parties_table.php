<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('role');              // vendedor | comprador
            $table->string('party_type');        // particular | autonomo | sociedad
            $table->string('full_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('tax_id');
            $table->string('tax_id_country', 2)->default('ES');
            $table->string('country', 2)->default('ES');
            $table->string('address');
            $table->string('postal_code');
            $table->string('city');
            $table->string('province')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('activity')->nullable();
            $table->string('representative_name')->nullable();
            $table->string('representative_tax_id')->nullable();
            $table->string('eori')->nullable();
            $table->boolean('registered_vat')->default(false);
            $table->boolean('acting_in_own_name')->default(true);
            $table->string('signature_city')->nullable();
            $table->date('signature_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parties');
    }
};
