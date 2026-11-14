<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->restrictOnDelete();

            $table->unsignedBigInteger('parent_id')->nullable();

            $table->string('code', 100);
            $table->string('name');
            $table->string('type', 30);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['company_id', 'id']);
            $table->unique(['company_id', 'code']);

            $table->foreign(['company_id', 'parent_id'])
                ->references(['company_id', 'id'])
                ->on('locations')
                ->restrictOnDelete();

            $table->index(['company_id', 'parent_id']);
            $table->index(['company_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
