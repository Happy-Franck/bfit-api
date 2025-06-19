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
        Schema::create('equipment_training', function (Blueprint $table) {
            $table->unsignedBigInteger('training_id');
            $table->unsignedBigInteger('equipment_id');
            $table->timestamps();
            $table->primary(['training_id', 'equipment_id']);
            $table->foreign('training_id')->references('id')->on('trainings')->onDelete('cascade');
            $table->foreign('equipment_id')->references('id')->on('equipments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_training');
    }
}; 