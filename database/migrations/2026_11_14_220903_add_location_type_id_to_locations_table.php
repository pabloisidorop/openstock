<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->unsignedBigInteger('location_type_id')->nullable();

            $table->foreign(['company_id', 'location_type_id'])
                ->references(['company_id', 'id'])
                ->on('location_types')
                ->restrictOnDelete();

            $table->index(['company_id', 'location_type_id']);
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['company_id', 'location_type_id']);
            $table->dropIndex(['company_id', 'location_type_id']);
            $table->dropColumn('location_type_id');
        });
    }
};
