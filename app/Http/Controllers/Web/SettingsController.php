<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Settings\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(SettingsService $settings): View
    {
        return view('pages.settings', [
            'settings' => Setting::orderBy('scope')->orderBy('key')->get(),
        ]);
    }

    public function update(Request $request, SettingsService $settings): RedirectResponse
    {
        $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ($request->input('settings') as $key => $value) {
            if (str_starts_with($key, '__')) {
                continue;
            }

            $scope = str_contains($key, ':') ? explode(':', $key)[0] : Setting::SCOPE_ORGANIZATION;
            $cleanKey = str_contains($key, ':') ? explode(':', $key)[1] : $key;

            $settings->set($cleanKey, $value, $scope);
        }

        return redirect()->route('settings')->with('status', 'Settings saved.');
    }

    public function savePreference(Request $request, SettingsService $settings): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'key' => ['required', 'string', 'max:100'],
            'value' => ['required', 'string', 'max:500'],
        ]);

        $key = 'pref_' . $request->input('key') . '_' . $request->user()->id;
        $settings->set($key, $request->input('value'), Setting::SCOPE_SYSTEM);

        return response()->json(['status' => 'saved', 'key' => $key, 'value' => $request->input('value')]);
    }
}
