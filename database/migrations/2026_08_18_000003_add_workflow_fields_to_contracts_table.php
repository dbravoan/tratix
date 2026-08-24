<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('access_token', 36)->nullable()->unique()->after('status');
            $table->string('final_pdf_path')->nullable()->after('access_token');
            $table->string('final_hash', 64)->nullable()->after('final_pdf_path');
            $table->timestamp('sealed_at')->nullable()->after('final_hash');
            $table->unsignedInteger('signed_version')->nullable()->after('sealed_at');
            $table->timestamp('review_deadline')->nullable()->after('signed_version');
            $table->string('invited_email')->nullable()->after('review_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['access_token', 'final_pdf_path', 'final_hash', 'sealed_at', 'signed_version', 'review_deadline', 'invited_email']);
        });
    }
};
