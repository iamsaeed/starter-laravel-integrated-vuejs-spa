<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\TemplatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WelcomeEmailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a welcome email is sent when a user registers.
     */
    public function test_welcome_email_is_sent_when_user_registers(): void
    {
        // Seed email templates
        $this->seed(\Database\Seeders\EmailTemplatesSeeder::class);

        Notification::fake();

        // Register a new user
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ]);

        $response->assertStatus(201);

        // Get the created user
        $user = User::where('email', 'test@example.com')->first();

        // Assert that the welcome email notification was sent
        Notification::assertSentTo(
            $user,
            TemplatedNotification::class,
            function ($notification) {
                return $notification->getTemplateKey() === 'user_welcome';
            }
        );
    }

    /**
     * Test that the welcome email contains correct data.
     */
    public function test_welcome_email_contains_user_data(): void
    {
        // Seed email templates
        $this->seed(\Database\Seeders\EmailTemplatesSeeder::class);

        Notification::fake();

        // Register a new user
        $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ]);

        $user = User::where('email', 'john@example.com')->first();

        // Assert notification was sent with correct data
        Notification::assertSentTo(
            $user,
            TemplatedNotification::class,
            function ($notification) {
                $data = $notification->getData();

                return $notification->getTemplateKey() === 'user_welcome'
                    && isset($data['user'])
                    && $data['user']->email === 'john@example.com';
            }
        );
    }
}
