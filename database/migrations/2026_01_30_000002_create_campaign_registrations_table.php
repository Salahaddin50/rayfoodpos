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
        Schema::create('campaign_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('verification_code')->nullable()->comment('For verification purposes');
            $table->tinyInteger('status')->default(\App\Enums\Status::INACTIVE)
                ->comment('0=Pending, 5=Active/Verified, 10=Suspended');
            $table->integer('purchase_count')->default(0)->comment('Track purchases for item-based campaigns');
            $table->integer('rewards_claimed')->default(0)->comment('Track how many free items claimed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->unique(['campaign_id', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaign_registrations');
    }
};
