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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->tinyInteger('type')->default(\App\Enums\CampaignType::PERCENTAGE)
                ->comment(\App\Enums\CampaignType::PERCENTAGE . '=Percentage, ' . \App\Enums\CampaignType::ITEM . '=Item');
            $table->decimal('discount_value', 19, 6)->nullable()->comment('Percentage value for % type');
            $table->unsignedBigInteger('free_item_id')->nullable()->comment('Item ID for item type');
            $table->integer('required_purchases')->nullable()->comment('Number of purchases required for item type (e.g., buy 8 get 1 free)');
            $table->tinyInteger('status')->default(\App\Enums\Status::ACTIVE)
                ->comment(\App\Enums\Status::ACTIVE . '=' . trans('statuse.' . \App\Enums\Status::ACTIVE) . ', ' . \App\Enums\Status::INACTIVE . '=' . trans('statuse.' . \App\Enums\Status::INACTIVE));
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('creator_type')->nullable();
            $table->bigInteger('creator_id')->nullable();
            $table->string('editor_type')->nullable();
            $table->bigInteger('editor_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaigns');
    }
};
