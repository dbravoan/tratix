<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tax_id', 32)->nullable()->after('email');
            $table->string('company_name', 255)->nullable()->after('tax_id');
            $table->string('party_type', 32)->default('particular')->after('company_name');
            $table->string('phone', 32)->nullable()->after('party_type');
            $table->string('address', 255)->nullable()->after('phone');
            $table->string('postal_code', 16)->nullable()->after('address');
            $table->string('city', 100)->nullable()->after('postal_code');
            $table->string('country', 8)->default('ES')->after('city');

            $table->boolean('notify_comments')->default(true)->after('country');
            $table->boolean('notify_proposals')->default(true)->after('notify_comments');
            $table->boolean('notify_signatures')->default(true)->after('notify_proposals');
            $table->boolean('notify_summary')->default(true)->after('notify_signatures');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'tax_id',
                'company_name',
                'party_type',
                'phone',
                'address',
                'postal_code',
                'city',
                'country',
                'notify_comments',
                'notify_proposals',
                'notify_signatures',
                'notify_summary',
            ]);
        });
    }
};
