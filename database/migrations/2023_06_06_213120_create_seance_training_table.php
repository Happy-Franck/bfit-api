<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('seance_training', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('training_id');
            $table->unsignedBigInteger('seance_id');
            $table->integer('series')->nullable();
            $table->integer('repetitions')->nullable();
            $table->integer('duree')->nullable();
            $table->foreign('training_id')->references('id')->on('trainings')->onDelete('cascade');
            $table->foreign('seance_id')->references('id')->on('seances')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seance_training');
    }
};
