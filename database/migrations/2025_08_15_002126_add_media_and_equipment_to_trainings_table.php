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
        Schema::table('trainings', function (Blueprint $table) {
            if (!Schema::hasColumn('trainings', 'image_homme')) {
                $table->string('image_homme')->nullable()->after('image');
            }
            if (!Schema::hasColumn('trainings', 'image_femme')) {
                $table->string('image_femme')->nullable()->after('image_homme');
            }
            if (!Schema::hasColumn('trainings', 'equipment_id')) {
                $table->unsignedBigInteger('equipment_id')->nullable()->after('user_id');
                $table->foreign('equipment_id')->references('id')->on('equipments')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            if (Schema::hasColumn('trainings', 'equipment_id')) {
                $table->dropForeign(['equipment_id']);
                $table->dropColumn('equipment_id');
            }
            if (Schema::hasColumn('trainings', 'image_homme')) {
                $table->dropColumn('image_homme');
            }
            if (Schema::hasColumn('trainings', 'image_femme')) {
                $table->dropColumn('image_femme');
            }
        });
    }
};
