<?php

namespace App\Http\Controllers;

use App\Domain\Reports\DashboardWidgets;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function dashboard(Request $request): View
    {
        return view('dashboard', ['widgets' => app(DashboardWidgets::class)->for($request->user())]);
    }

    public function users(): View
    {
        return view('pages.settings-users');
    }

    public function personnel(): View
    {
        return view('pages.personnel');
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
