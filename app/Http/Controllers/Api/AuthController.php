<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\ImpersonationService;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        public AuthService $authService,
        public SettingsService $settingsService,
        public ImpersonationService $impersonationService
    ) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());
        $result['user']->load('roles');

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 201);
    }

    /**
     * Login a user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());
        $result['user']->load('roles');

        // Get user settings
        $settings = $this->settingsService->getForUser($result['user']);

        // Get impersonation status
        $impersonationStatus = $this->impersonationService->getStatus();

        return response()->json([
            'message' => 'Login successful.',
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'settings' => $settings,
            'impersonation' => $impersonationStatus,
        ]);
    }

    /**
     * Logout the authenticated user.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logout successful.',
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles');

        // Get user settings
        $settings = $this->settingsService->getForUser($user);

        // Get impersonation status
        $impersonationStatus = $this->impersonationService->getStatus();

        return response()->json([
            'user' => new UserResource($user),
            'settings' => $settings,
            'impersonation' => $impersonationStatus,
        ]);
    }

    /**
     * Send password reset link.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->sendPasswordResetLink($request->validated('email'));

        return response()->json([
            'message' => 'Password reset link sent to your email.',
        ]);
    }

    /**
     * Reset password.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $user = $this->authService->resetPassword($request->validated());

        return response()->json([
            'message' => 'Password reset successfully.',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->authService->updateProfile($request->user(), $request->validated());
        $user->load('roles');

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Change user password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword($request->user(), $request->validated('password'));

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * Logout all sessions (revoke all tokens).
     */
    public function logoutAllSessions(Request $request): JsonResponse
    {
        $this->authService->logoutAllSessions($request->user());

        return response()->json([
            'message' => 'Logged out from all sessions successfully.',
        ]);
    }

    /**
     * Logout all sessions except the current one.
     */
    public function logoutOtherSessions(Request $request): JsonResponse
    {
        $currentTokenId = $request->user()->currentAccessToken()->id;
        $this->authService->logoutOtherSessions($request->user(), $currentTokenId);

        return response()->json([
            'message' => 'Logged out from all other sessions successfully.',
        ]);
    }

    /**
     * Check if an email exists in the system (public endpoint for invitation flow).
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $exists = $this->authService->checkEmailExists($request->input('email'));

        return response()->json([
            'exists' => $exists,
            'email' => $request->input('email'),
        ]);
    }
}
