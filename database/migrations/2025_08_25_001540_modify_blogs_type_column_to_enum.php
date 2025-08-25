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
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        
        Schema::table('blogs', function (Blueprint $table) {
            $table->enum('type', ['tofu', 'mofu', 'bofu'])->nullable()->after('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('type')->nullable()->after('image');
        });
    }
};
