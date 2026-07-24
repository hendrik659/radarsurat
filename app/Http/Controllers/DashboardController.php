<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with live account-management summary data.
     */
    public function __invoke(): View
    {
        return view('dashboard.index', [
            'totalUsers' => User::query()->count(),
            'activeUsers' => User::query()->where('is_active', true)->count(),
            'totalDivisions' => Division::query()->count(),
        ]);
    }
}
