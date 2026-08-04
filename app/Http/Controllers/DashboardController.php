<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function dashboard(): View
    {
        return view('dashboard');
    }

    public function settings(): View
    {
        return view('pages.settings');
    }

    public function users(): View
    {
        return view('pages.settings-users');
    }

    public function personnel(): View
    {
        return view('pages.personnel');
    }

    public function properties(): View
    {
        return view('pages.properties');
    }

    public function propertyCreate(): View
    {
        return view('pages.property-create');
    }

    public function reports(): View
    {
        return view('pages.reports');
    }

    public function approvals(): View
    {
        return view('pages.approvals');
    }

    public function cleanerTools(): View
    {
        return view('pages.cleaner-tools');
    }
}
