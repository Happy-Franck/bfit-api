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
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_attribute_id');
            $table->string('value'); // Ex: "S", "Rouge", "5kg"
            $table->string('label'); // Ex: "Petit", "Rouge", "5 kilogrammes"
            $table->string('color_code')->nullable(); // Pour les couleurs : #FF0000
            $table->decimal('price_modifier', 10, 2)->default(0); // Modification prix (+/-)
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('product_attribute_id')->references('id')->on('product_attributes')->onDelete('cascade');
            $table->unique(['product_attribute_id', 'value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
    }
}; 