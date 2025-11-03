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
        Schema::create('timezones', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('abbreviation')->nullable();
            $table->string('abbreviation_dst')->nullable();
            $table->integer('offset');
            $table->integer('offset_dst')->nullable();
            $table->string('offset_formatted');
            $table->boolean('uses_dst')->default(false);
            $table->string('display_name');
            $table->string('city_name')->nullable();
            $table->string('region');
            $table->json('coordinates')->nullable();
            $table->bigInteger('population')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(999);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('region');
            $table->index(['is_active', 'is_primary']);
            $table->index('uses_dst');
        });

        // Seed immediately after table creation for production deployments (skip in testing)
        if (! app()->environment('testing')) {
            Artisan::call('db:seed', [
                '--class' => 'TimezonesSeeder',
                '--force' => true,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timezones');
    }
};
