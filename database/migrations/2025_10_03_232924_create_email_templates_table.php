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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Unique identifier like user_welcome, password_reset');
            $table->string('name')->comment('Human-readable name');
            $table->text('subject_template')->comment('Blade template for subject');
            $table->longText('body_content')->comment('Complete Blade template with full HTML');
            $table->text('preview_thumbnail')->nullable()->comment('Cached preview thumbnail');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'key']);
        });

        // Seed default email templates
        (new \Database\Seeders\EmailTemplatesSeeder)->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
