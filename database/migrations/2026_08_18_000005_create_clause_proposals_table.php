<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clause_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_version_id')->nullable()->constrained()->nullOnDelete();
            $table->string('clause_key');
            $table->string('clause_title')->nullable();
            $table->text('original_text');
            $table->text('proposed_text');
            $table->string('proposed_by');              // seller | buyer | creator
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // pending | approved | rejected
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clause_proposals');
    }
};
