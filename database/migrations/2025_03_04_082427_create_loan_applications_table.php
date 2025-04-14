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
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('agent_id')->constrained('agents');
            $table->foreignId('module_id')->constrained('loan_modules');
            $table->string('biro')->nullable();
            $table->string('banca')->nullable();
            $table->string('special_request')->nullable();
            $table->decimal('rates', 5, 2)->nullable();
            $table->json('document_checklist')->nullable();
            $table->date('date_received')->nullable();
            $table->date('date_approved')->nullable();
            $table->date('date_disbursed')->nullable();
            $table->date('date_rejected')->nullable();
            $table->integer('tenure_applied')->nullable();
            $table->integer('tenure_approved')->nullable();
            $table->integer('amount_applied')->nullable();
            $table->integer('amount_approved')->nullable();
            $table->integer('amount_disbursed')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_applications');
    }
};
