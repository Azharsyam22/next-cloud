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
        Schema::create('shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('shareable_type');
            $table->unsignedBigInteger('shareable_id');
            $table->string('token', 64)->unique();
            $table->string('permission', 20)->default('view');
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('shared_with_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['shareable_type', 'shareable_id']);
            $table->index('token');
            $table->index('shared_with_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shares');
    }
};
