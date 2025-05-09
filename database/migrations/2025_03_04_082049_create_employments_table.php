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
        Schema::create('employments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->string('job_title')->nullable();
            $table->string('phone_num')->nullable();
            $table->string('bank')->nullable();
            $table->string('pension')->nullable();
            $table->string('address')->nullable();
            $table->string('company_name')->nullable();
            $table->date('date_joined')->nullable();
            $table->string('account_num')->nullable();
            $table->string('emp_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employments');
    }
};
