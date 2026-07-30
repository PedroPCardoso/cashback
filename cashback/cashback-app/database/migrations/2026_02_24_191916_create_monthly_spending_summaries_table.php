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
        Schema::create('monthly_spending_summaries', function (Blueprint $table) {
            $table->id();
            $table->uuid('category_id');
            $table->integer('year');
            $table->integer('month');
            $table->bigInteger('total_spent_cents')->default(0);
            $table->bigInteger('cashback_earned_cents')->default(0);
            $table->string('status'); // within_limit, exceeded
            $table->timestamps();

            $table->unique(['category_id', 'year', 'month'], 'category_month_unique');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_spending_summaries');
    }
};
