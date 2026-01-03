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
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // QR code string/token
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('points_awarded')->nullable(); // Points to award on redemption
            $table->dateTime('start_at')->nullable(); // Activation date/time
            $table->dateTime('expires_at')->nullable(); // Expiration date/time
            $table->boolean('is_active')->default(true);
            $table->integer('per_customer_limit')->nullable(); // null = unlimited per customer
            $table->integer('total_limit')->nullable(); // null = unlimited total uses
            $table->integer('total_used_count')->default(0); // Cached counter
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index('code');
            $table->index('is_active');
            $table->index('start_at');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
