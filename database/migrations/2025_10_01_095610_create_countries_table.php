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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique();
            $table->string('code_alpha3', 3)->unique();
            $table->string('numeric_code', 3);
            $table->string('name');
            $table->json('native_name')->nullable();
            $table->string('capital')->nullable();
            $table->string('region');
            $table->string('subregion')->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->string('currency_name')->nullable();
            $table->string('currency_symbol')->nullable();
            $table->string('phone_code')->nullable();
            $table->string('flag_emoji', 10)->nullable();
            $table->string('flag_svg')->nullable();
            $table->json('languages')->nullable();
            $table->string('tld')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_eu_member')->default(false);
            $table->integer('display_order')->default(999);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['region', 'subregion']);
            $table->index('is_active');
            $table->index('display_order');
        });

        // Seed immediately after table creation for production deployments (skip in testing)
        if (! app()->environment('testing')) {
            Artisan::call('db:seed', [
                '--class' => 'CountriesSeeder',
                '--force' => true,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
