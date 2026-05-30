<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifak_providers', function (Blueprint $table) {
            $table->id();
            $table->string('driver', 50)->unique()->comment('نام درایور: smsir, mediana, ...');
            $table->json('config')->comment('تنظیمات درایور به صورت JSON');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('notifak_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50);
            $table->string('to', 500);
            $table->string('type', 20)->default('normal'); // normal | pattern
            $table->text('message')->nullable();
            $table->string('status', 20)->default('sent'); // sent | failed
            $table->json('response')->nullable();
            $table->timestamps();

            $table->index('provider');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifak_logs');
        Schema::dropIfExists('notifak_providers');
    }
};
