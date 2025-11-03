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
        Schema::create('country_timezone', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->foreignId('timezone_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->json('regions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['country_id', 'timezone_id']);
            $table->index('is_primary');
        });

        // Seed immediately after table creation for production deployments (skip in testing)
        if (! app()->environment('testing')) {
            Artisan::call('db:seed', [
                '--class' => 'CountryTimezoneSeeder',
                '--force' => true,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_timezone');
    }
};
