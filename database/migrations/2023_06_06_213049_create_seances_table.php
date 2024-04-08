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
        Schema::create('seances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->unsignedBigInteger('coach_id')->nullable();
            $table->unsignedBigInteger('challenger_id');
            $table->string('img_debut')->nullable();
            $table->string('img_fin')->nullable();
            $table->boolean('validated')->nullable();
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('coach_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('challenger_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seances');
    }
};
