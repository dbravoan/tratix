<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signatures', function (Blueprint $table) {
            $table->boolean('otp_verified')->default(false)->after('consent_text');
            $table->string('otp_verification_id', 64)->nullable()->after('otp_verified');
        });

        Schema::create('consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('signer_email');
            $table->string('role');
            $table->string('consent_type');          // signing | data_processing
            $table->string('policy_version', 32)->nullable();
            $table->timestamp('accepted_at');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consents');
        Schema::table('signatures', function (Blueprint $table) {
            $table->dropColumn(['otp_verified', 'otp_verification_id']);
        });
    }
};
