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
        Schema::create('mix_builder_bases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mix_builder_id')->nullable(); // Nullable for global bases (all builders can use)
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->timestamps();

            // Unique constraint: same base can't be assigned twice to same builder
            $table->unique(['mix_builder_id', 'product_id']);
            
            // Indexes
            $table->index('mix_builder_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mix_builder_bases');
    }
};
