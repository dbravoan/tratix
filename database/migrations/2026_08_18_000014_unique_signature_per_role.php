<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One signature per role per contract: hard DB-level guard against
        // duplicate signatures under concurrent requests.
        Schema::table('signatures', function (Blueprint $table) {
            $table->unique(['contract_id', 'party_role']);
        });
    }

    public function down(): void
    {
        Schema::table('signatures', function (Blueprint $table) {
            $table->dropUnique(['contract_id', 'party_role']);
        });
    }
};
