<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaign_completions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('whatsapp', 32);
            $table->timestamp('completed_at');
            $table->unsignedBigInteger('final_order_id')->nullable(); // The order where they redeemed
            $table->timestamps();

            // Prevent duplicate completions
            $table->unique(['campaign_id', 'branch_id', 'whatsapp']);
            
            // Indexes for efficient lookups
            $table->index(['whatsapp', 'branch_id']);
            $table->index('campaign_id');
            
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaign_completions');
    }
};
