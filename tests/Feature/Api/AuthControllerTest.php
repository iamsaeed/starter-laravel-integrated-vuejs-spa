<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Notifications\TemplatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson(route('api.register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'role',
                    'is_admin',
                    'created_at',
                    'updated_at',
                ],
                'token',
            ])
            ->assertJson([
                'user' => [
                    'role' => 'user',
                    'is_admin' => false,
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // Verify user role was assigned
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user->role());
        $this->assertEquals('user', $user->role()->slug);
        $this->assertFalse($user->isAdmin());
    }

    public function test_user_cannot_register_with_invalid_data(): void
    {
        $response = $this->postJson(route('api.register'), [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'pass',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_user_cannot_register_with_existing_email(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson(route('api.register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson(route('api.login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                ],
                'token',
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson(route('api.login'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_cannot_login_with_missing_data(): void
    {
        $response = $this->postJson(route('api.login'), [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(route('api.logout'));

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logout successful.',
            ]);

        $this->assertCount(0, $user->tokens);
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson(route('api.logout'));

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('api.me'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson(route('api.me'));

        $response->assertStatus(401);
    }

    public function test_user_can_request_password_reset_link(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['email' => 'test@example.com'])
            ->andReturn(Password::RESET_LINK_SENT);

        $response = $this->postJson(route('api.forgot-password'), [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Password reset link sent to your email.',
            ]);
    }

    public function test_user_cannot_request_password_reset_with_invalid_email(): void
    {
        $response = $this->postJson(route('api.forgot-password'), [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_reset_password(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        Password::shouldReceive('reset')
            ->once()
            ->andReturn(Password::PASSWORD_RESET);

        $response = $this->postJson(route('api.reset-password'), [
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => 'valid-token',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_user_cannot_reset_password_with_invalid_data(): void
    {
        $response = $this->postJson(route('api.reset-password'), [
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
            'token' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password', 'token']);
    }

    public function test_password_reset_email_is_sent(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson(route('api.forgot-password'), [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200);

        Notification::assertSentTo($user, TemplatedNotification::class, function ($notification, $channels) use ($user) {
            $data = $notification->getData();

            return $notification->getTemplateKey() === 'password_reset' &&
                   isset($data['reset_url']) &&
                   str_contains($data['reset_url'], 'reset-password') &&
                   str_contains($data['reset_url'], 'token=') &&
                   str_contains($data['reset_url'], 'email='.urlencode($user->email));
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldpassword123'),
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson(route('api.reset-password'), [
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => $token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);

        $user->refresh();

        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_password_cannot_be_reset_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->postJson(route('api.reset-password'), [
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => 'invalid-token',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $user->refresh();

        $this->assertTrue(Hash::check('oldpassword123', $user->password));
    }

    public function test_password_reset_token_expires(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldpassword123'),
        ]);

        $token = Password::createToken($user);

        $this->travel(2)->hours();

        $response = $this->postJson(route('api.reset-password'), [
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => $token,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $user->refresh();

        $this->assertTrue(Hash::check('oldpassword123', $user->password));
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson(route('api.profile.update'), [
                'name' => 'New Name',
                'email' => 'new@example.com',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Profile updated successfully.',
                'user' => [
                    'name' => 'New Name',
                    'email' => 'new@example.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    public function test_authenticated_user_cannot_update_profile_with_existing_email(): void
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $user = User::factory()->create(['email' => 'user@example.com']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson(route('api.profile.update'), [
                'name' => 'Test User',
                'email' => 'existing@example.com',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_cannot_update_profile_with_invalid_data(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson(route('api.profile.update'), [
                'name' => '',
                'email' => 'invalid-email',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_authenticated_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson(route('api.password.change'), [
                'current_password' => 'oldpassword123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Password changed successfully.',
            ]);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_authenticated_user_cannot_change_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson(route('api.password.change'), [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);

        $user->refresh();
        $this->assertTrue(Hash::check('oldpassword123', $user->password));
    }

    public function test_authenticated_user_cannot_change_password_with_invalid_data(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson(route('api.password.change'), [
                'current_password' => '',
                'password' => 'short',
                'password_confirmation' => 'different',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password', 'password']);
    }

    public function test_authenticated_user_can_logout_all_sessions(): void
    {
        $user = User::factory()->create();
        $token1 = $user->createToken('device1')->plainTextToken;
        $token2 = $user->createToken('device2')->plainTextToken;
        $token3 = $user->createToken('device3')->plainTextToken;

        $this->assertCount(3, $user->tokens);

        $response = $this->withHeader('Authorization', 'Bearer '.$token1)
            ->postJson(route('api.logout-all-sessions'));

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out from all sessions successfully.',
            ]);

        $user->refresh();
        $this->assertCount(0, $user->tokens);
    }

    public function test_authenticated_user_can_logout_other_sessions(): void
    {
        $user = User::factory()->create();
        $token1 = $user->createToken('device1');
        $token2 = $user->createToken('device2');
        $token3 = $user->createToken('device3');

        $this->assertCount(3, $user->tokens);

        $response = $this->withHeader('Authorization', 'Bearer '.$token1->plainTextToken)
            ->postJson(route('api.logout-other-sessions'));

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out from all other sessions successfully.',
            ]);

        $user->refresh();
        $this->assertCount(1, $user->tokens);
        $this->assertEquals($token1->accessToken->id, $user->tokens->first()->id);
    }

    public function test_unauthenticated_user_cannot_update_profile(): void
    {
        $response = $this->putJson(route('api.profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_change_password(): void
    {
        $response = $this->putJson(route('api.password.change'), [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_logout_all_sessions(): void
    {
        $response = $this->postJson(route('api.logout-all-sessions'));

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_logout_other_sessions(): void
    {
        $response = $this->postJson(route('api.logout-other-sessions'));

        $response->assertStatus(401);
    }

    public function test_login_returns_user_role_and_is_admin_flag(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole('admin');

        $response = $this->postJson(route('api.login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'role',
                    'is_admin',
                ],
            ])
            ->assertJson([
                'user' => [
                    'role' => 'admin',
                    'is_admin' => true,
                ],
            ]);
    }

    public function test_me_endpoint_returns_user_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('api.me'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'role',
                    'is_admin',
                ],
            ])
            ->assertJson([
                'user' => [
                    'role' => 'user',
                    'is_admin' => false,
                ],
            ]);
    }

    public function test_admin_user_has_is_admin_true(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('api.me'));

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'role' => 'admin',
                    'is_admin' => true,
                ],
            ]);

        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_regular_user_has_is_admin_false(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('api.me'));

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'role' => 'user',
                    'is_admin' => false,
                ],
            ]);

        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->hasRole('user'));
        $this->assertFalse($user->hasRole('admin'));
    }
}
