<?php

use App\Enums\ChannelStatus;
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
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->text('cookies')->nullable();
            $table->string('proxy')->nullable();
            $table->tinyInteger('status')->default(ChannelStatus::ACTIVE);
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->noActionOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
