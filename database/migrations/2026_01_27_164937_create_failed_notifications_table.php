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
        Schema::create('failed_notifications', function (Blueprint $table) {
            $table->id();
            $table->text('token'); // FCM token that failed
            $table->string('title')->nullable(); // Notification title
            $table->text('body')->nullable(); // Notification body
            $table->string('topic')->nullable(); // Topic name (e.g., 'new-order-found')
            $table->text('error_message')->nullable(); // Error message from FCM
            $table->timestamps();
            
            // Index for faster lookups
            $table->index('topic');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_notifications');
    }
};
