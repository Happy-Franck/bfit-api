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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('sku')->unique(); // Code unique pour la variante
            $table->string('name')->nullable(); // Nom spécifique de la variante
            $table->decimal('price', 10, 2); // Prix de cette variante
            $table->decimal('compare_price', 10, 2)->nullable(); // Prix de comparaison
            $table->integer('stock_quantity')->default(0);
            $table->integer('weight')->nullable(); // Poids en grammes
            $table->string('barcode')->nullable();
            $table->string('image')->nullable(); // Image spécifique à la variante
            $table->boolean('track_inventory')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('product_id')->references('id')->on('produits')->onDelete('cascade');
            $table->index(['product_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
}; 