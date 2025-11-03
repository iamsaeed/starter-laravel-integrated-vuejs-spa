<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountriesController extends Controller
{
    /**
     * Get all countries or filter by region.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Country::query()->active()->popular();

        if ($region = $request->query('region')) {
            $query->where('region', $region);
        }

        $countries = $query->get();

        return response()->json([
            'countries' => $countries,
        ]);
    }

    /**
     * Get a specific country with its timezones.
     */
    public function show(string $code): JsonResponse
    {
        $country = Country::with('timezones')->where('code', $code)->first();

        if (! $country) {
            return response()->json([
                'message' => 'Country not found.',
            ], 404);
        }

        return response()->json([
            'country' => $country,
        ]);
    }
}
