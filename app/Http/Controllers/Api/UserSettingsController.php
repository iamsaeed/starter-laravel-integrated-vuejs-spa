<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkUpdateSettingsRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSettingsController extends Controller
{
    public function __construct(public SettingsService $settingsService) {}

    /**
     * Get all current user's settings.
     */
    public function index(Request $request): JsonResponse
    {
        $group = $request->query('group');

        $settings = $this->settingsService->getForUser($request->user(), $group);

        return response()->json([
            'settings' => $settings,
        ]);
    }

    /**
     * Get a specific user setting by key.
     */
    public function show(Request $request, string $key): JsonResponse
    {
        $setting = $this->settingsService->getWithReference($key, 'user', $request->user()->id);

        if (! $setting) {
            return response()->json([
                'message' => 'Setting not found.',
            ], 404);
        }

        return response()->json([
            'setting' => [
                'key' => $setting->key,
                'value' => $setting->getTypedValue(),
                'type' => $setting->type,
                'group' => $setting->group,
                'label' => $setting->label,
                'description' => $setting->description,
                'icon' => $setting->icon,
                'referenceable' => $setting->referenceable,
            ],
        ]);
    }

    /**
     * Update current user's settings (bulk).
     */
    public function update(BulkUpdateSettingsRequest $request): JsonResponse
    {
        $settings = $request->input('settings');

        $results = $this->settingsService->bulkSet($settings, 'user', $request->user()->id);

        return response()->json([
            'message' => 'Settings updated successfully.',
            'settings' => collect($results)->mapWithKeys(function ($setting, $key) {
                return [$key => $setting->getTypedValue()];
            }),
        ]);
    }

    /**
     * Update a single user setting.
     */
    public function updateSingle(UpdateSettingRequest $request, string $key): JsonResponse
    {
        $value = $request->input('value');

        $setting = $this->settingsService->setForUser($request->user(), $key, $value);

        return response()->json([
            'message' => 'Setting updated successfully.',
            'setting' => [
                'key' => $setting->key,
                'value' => $setting->getTypedValue(),
            ],
        ]);
    }
}
