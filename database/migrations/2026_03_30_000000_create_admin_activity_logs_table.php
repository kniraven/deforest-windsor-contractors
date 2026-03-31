<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id')->nullable()->index();
            $table->string('listing_name')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('actor_type')->default('admin');
            $table->string('actor_name')->nullable();
            $table->string('actor_email')->nullable();
            $table->string('action', 100);
            $table->text('summary');
            $table->json('changes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};