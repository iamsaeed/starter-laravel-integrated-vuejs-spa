<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Timezone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimezonesController extends Controller
{
    /**
     * Get all timezones or filter by region/country.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Timezone::query()->active()->orderBy('display_order');

        if ($region = $request->query('region')) {
            $query->where('region', $region);
        }

        if ($countryId = $request->query('country_id')) {
            $query->byCountry((int) $countryId);
        }

        $timezones = $query->get();

        return response()->json([
            'timezones' => $timezones,
        ]);
    }

    /**
     * Get a specific timezone.
     */
    public function show(int $id): JsonResponse
    {
        $timezone = Timezone::with('countries')->find($id);

        if (! $timezone) {
            return response()->json([
                'message' => 'Timezone not found.',
            ], 404);
        }

        return response()->json([
            'timezone' => $timezone,
        ]);
    }
}
