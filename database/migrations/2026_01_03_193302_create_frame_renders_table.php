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
        Schema::create('frame_renders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('frame_id')->constrained('frames')->onDelete('cascade');
            $table->string('original_image_path'); // Uploaded photo path
            $table->string('rendered_image_path'); // Final rendered image path
            $table->integer('width')->nullable(); // Output width
            $table->integer('height')->nullable(); // Output height
            $table->string('format')->nullable(); // Output format (jpg/png)
            $table->timestamps();

            // Indexes
            $table->index('customer_id');
            $table->index('frame_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frame_renders');
    }
};
