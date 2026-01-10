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
        Schema::create('token_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('shift_date')->default(now()->toDateString());
            $table->integer('counter')->default(0);
            $table->string('prefix')->default('T');
            $table->timestamps();
            
            // Ensure one counter per branch per shift date
            $table->unique(['branch_id', 'shift_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('token_counters');
    }
};



