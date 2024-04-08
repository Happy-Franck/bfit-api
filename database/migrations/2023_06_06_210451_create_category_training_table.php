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
        Schema::create('category_training', function (Blueprint $table) {
            $table->unsignedBigInteger('training_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();
            $table->primary(['training_id', 'category_id']);
            $table->foreign('training_id')->references('id')->on('trainings')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_training');
    }
};
