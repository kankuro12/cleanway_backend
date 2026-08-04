<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Geocoding\GooglePlaces;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Backend-keyed Google Places proxy: the API key never reaches the browser.
 */
class PlacesController extends Controller
{
    public function autocomplete(Request $request, GooglePlaces $places): JsonResponse
    {
        abort_unless($request->user()->hasPermission('3.1'), 403);

        $request->validate(['input' => ['required', 'string', 'max:200']]);

        return response()->json(['data' => $places->autocomplete($request->string('input'))]);
    }

    public function details(Request $request, GooglePlaces $places): JsonResponse
    {
        abort_unless($request->user()->hasPermission('3.1'), 403);

        $request->validate(['place_id' => ['required', 'string', 'max:255']]);

        return response()->json(['data' => $places->placeDetails($request->string('place_id'))]);
    }
}
