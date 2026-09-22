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
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_type')->default('public')->index()->after('email');
            $table->string('external_id')->nullable()->index()->after('account_type');
            $table->unsignedBigInteger('quota_bytes')->default(config('cloudcampus.default_quota_bytes', 5368709120))->after('password');
            $table->unsignedBigInteger('used_bytes')->default(0)->after('quota_bytes');
            $table->string('avatar_path')->nullable()->after('used_bytes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['account_type']);
            $table->dropIndex(['external_id']);
            $table->dropColumn([
                'account_type',
                'external_id',
                'quota_bytes',
                'used_bytes',
                'avatar_path',
            ]);
        });
    }
};
