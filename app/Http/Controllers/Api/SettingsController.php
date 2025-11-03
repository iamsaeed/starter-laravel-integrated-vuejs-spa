<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(public SettingsService $settingsService) {}

    /**
     * Get all settings or filtered by group.
     */
    public function index(Request $request): JsonResponse
    {
        $group = $request->query('group');
        $scope = $request->query('scope', 'global');

        if ($group) {
            $settings = $this->settingsService->getByGroup($group, $scope);
        } else {
            $settings = $this->settingsService->getAllByScope($scope);
        }

        return response()->json([
            'settings' => $settings,
        ]);
    }

    /**
     * Get a specific setting by key.
     */
    public function show(string $key): JsonResponse
    {
        $setting = $this->settingsService->getWithReference($key);

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
     * Create or update a setting.
     */
    public function store(UpdateSettingRequest $request): JsonResponse
    {
        $key = $request->input('key');
        $value = $request->input('value');

        $setting = $this->settingsService->set($key, $value);

        return response()->json([
            'message' => 'Setting saved successfully.',
            'setting' => [
                'key' => $setting->key,
                'value' => $setting->getTypedValue(),
            ],
        ], 201);
    }

    /**
     * Update a specific setting.
     */
    public function update(UpdateSettingRequest $request, string $key): JsonResponse
    {
        $value = $request->input('value');

        $setting = $this->settingsService->set($key, $value);

        return response()->json([
            'message' => 'Setting updated successfully.',
            'setting' => [
                'key' => $setting->key,
                'value' => $setting->getTypedValue(),
            ],
        ]);
    }

    /**
     * Delete a setting.
     */
    public function destroy(string $key): JsonResponse
    {
        $deleted = $this->settingsService->delete($key);

        if (! $deleted) {
            return response()->json([
                'message' => 'Setting not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Setting deleted successfully.',
        ]);
    }

    /**
     * Get all setting groups.
     */
    public function groups(): JsonResponse
    {
        $groups = config('settings.groups');

        // Get count of settings per group
        $groupsWithCount = collect($groups)->map(function ($groupData, $key) {
            return array_merge($groupData, [
                'key' => $key,
                'count' => Setting::where('group', $key)->count(),
            ]);
        })->values();

        return response()->json([
            'groups' => $groupsWithCount,
        ]);
    }

    /**
     * Get predefined options from setting_lists.
     */
    public function lists(string $key): JsonResponse
    {
        $lists = \App\Models\SettingList::where('key', $key)
            ->active()
            ->orderBy('order')
            ->get();

        return response()->json([
            'lists' => $lists,
        ]);
    }
}
