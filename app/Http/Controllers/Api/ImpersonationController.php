<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ImpersonationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    public function __construct(
        protected ImpersonationService $impersonationService
    ) {}

    /**
     * Start impersonating a user
     */
    public function impersonate(Request $request, int $userId): JsonResponse
    {
        $admin = $request->user();

        if (! $admin->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Only administrators can impersonate users.',
            ], 403);
        }

        $targetUser = User::findOrFail($userId);

        try {
            $this->impersonationService->impersonate($admin, $targetUser);

            return response()->json([
                'message' => 'Successfully impersonating user.',
                'user' => [
                    'id' => $targetUser->id,
                    'name' => $targetUser->name,
                    'email' => $targetUser->email,
                ],
                'impersonation' => $this->impersonationService->getStatus(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Stop impersonating and return to admin account
     */
    public function stopImpersonating(Request $request): JsonResponse
    {
        if (! $this->impersonationService->isImpersonating()) {
            return response()->json([
                'message' => 'Not currently impersonating.',
            ], 400);
        }

        try {
            $this->impersonationService->stopImpersonating();

            return response()->json([
                'message' => 'Successfully stopped impersonating.',
                'user' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get impersonation status
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'data' => $this->impersonationService->getStatus(),
        ]);
    }
}
