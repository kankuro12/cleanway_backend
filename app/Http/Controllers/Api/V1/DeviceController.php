<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
            'platform' => ['nullable', Rule::in(['web', 'android', 'ios'])],
            'device_id' => ['nullable', 'string', 'max:100'],
        ]);

        $device = UserDevice::updateOrCreate(
            ['fcm_token' => $request->string('fcm_token')],
            [
                'user_id' => $request->user()->id,
                'platform' => $request->string('platform', 'web')->toString(),
                'device_id' => $request->string('device_id'),
                'last_seen_at' => now(),
            ]
        );

        return response()->json(['data' => ['id' => $device->id, 'platform' => $device->platform]], 201);
    }

    public function destroy(Request $request, string $token): JsonResponse
    {
        UserDevice::where('user_id', $request->user()->id)
            ->where('fcm_token', $token)
            ->delete();

        return response()->json(['data' => null]);
    }
}
