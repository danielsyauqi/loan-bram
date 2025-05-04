<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'pending_email')) {
                $table->string('pending_email')->nullable();
            }
            if (!Schema::hasColumn('users', 'pending_email_token')) {
                $table->string('pending_email_token', 40)->nullable();
            }
            if (!Schema::hasColumn('users', 'pending_email_sent_at')) {
                $table->timestamp('pending_email_sent_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('pending_email');
            $table->dropColumn('pending_email_token');
            $table->dropColumn('pending_email_sent_at');
        });
    }
};
