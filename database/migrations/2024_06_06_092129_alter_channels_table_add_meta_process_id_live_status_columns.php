<?php

use App\Enums\LiveStatusEnum;
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
        Schema::table('channels', function (Blueprint $table) {
            $table->json('meta')->nullable()->after('device_id');
            $table->string('process_id')->nullable()->after('meta');
            $table->string('live_status')->default(LiveStatusEnum::OFFLINE)->after('process_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn('meta');
            $table->dropColumn('process_id');
            $table->dropColumn('live_status');
        });
    }
};
