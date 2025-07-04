<?php

use App\Enums\UserLevel;
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
            $table->integer('credit')->nullable()->default(0)->after('remember_token');
            $table->tinyInteger('level')->nullable()->default(UserLevel::FREE)->after('credit');
            $table->dateTime('subscription_ends_at')->nullable()->default(null)->after('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('credit');
            $table->dropColumn('level');
            $table->dropColumn('subscription_ends_at');
        });
    }
};
