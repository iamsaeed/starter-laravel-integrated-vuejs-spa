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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->json('value');
            $table->enum('type', ['string', 'integer', 'boolean', 'array', 'json', 'reference']);
            $table->string('group');
            $table->enum('scope', ['global', 'user', 'admin'])->default('global');
            $table->string('icon')->nullable();
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('is_encrypted')->default(false);
            $table->json('validation_rules')->nullable();
            $table->nullableMorphs('settable');
            $table->nullableMorphs('referenceable');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['key', 'scope', 'settable_type', 'settable_id']);
            $table->index(['group', 'scope']);
        });

        // Seed immediately after table creation for production deployments (skip in testing)
        if (! app()->environment('testing')) {
            Artisan::call('db:seed', [
                '--class' => 'SettingsSeeder',
                '--force' => true,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
