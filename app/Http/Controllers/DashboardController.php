<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\SensitiveDataCategory;
use App\Models\Transfer;
use App\Models\TransferDetection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isUser = $user->isUser();

        // Base query for transfers
        $transferQuery = Transfer::query();
        if ($isUser) {
            $transferQuery->where('user_id', $user->id);
        }

        $totalTransfers = (clone $transferQuery)->count();
        $allowedCount = (clone $transferQuery)->where('decision', 'allowed')->count();
        $blockedCount = (clone $transferQuery)->where('decision', 'blocked')->count();
        $flaggedCount = (clone $transferQuery)->where('decision', 'flagged')->count();

        // Base query for alerts
        $alertQuery = Alert::query();
        if ($isUser) {
            $alertQuery->where('user_id', $user->id);
        }

        $activeAlerts = (clone $alertQuery)->whereIn('status', ['open', 'under_review'])->count();
        $criticalAlerts = (clone $alertQuery)->where('severity', 'critical')->whereIn('status', ['open', 'under_review'])->count();

        // Recent transfers
        $recentTransfers = (clone $transferQuery)
            ->with(['user', 'detections'])
            ->latest()
            ->take(6)
            ->get();

        // Recent alerts
        $recentAlerts = (clone $alertQuery)
            ->with(['user', 'transfer'])
            ->latest()
            ->take(5)
            ->get();

        // Detection breakdown by category
        $detectionStats = TransferDetection::selectRaw('detection_technique, count(*) as total')
            ->groupBy('detection_technique')
            ->pluck('total', 'detection_technique')
            ->toArray();

        // System users count (for Admin)
        $totalUsers = User::count();
        $activeCategories = SensitiveDataCategory::where('is_active', true)->count();

        // Transfer trend data (last 7 days)
        $dates = [];
        $trendAllowed = [];
        $trendBlocked = [];
        $trendFlagged = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $dates[] = now()->subDays($i)->format('M d');

            $dayQuery = (clone $transferQuery)->whereDate('created_at', $day);
            $trendAllowed[] = (clone $dayQuery)->where('decision', 'allowed')->count();
            $trendBlocked[] = (clone $dayQuery)->where('decision', 'blocked')->count();
            $trendFlagged[] = (clone $dayQuery)->where('decision', 'flagged')->count();
        }

        return view('dashboard', compact(
            'totalTransfers',
            'allowedCount',
            'blockedCount',
            'flaggedCount',
            'activeAlerts',
            'criticalAlerts',
            'recentTransfers',
            'recentAlerts',
            'detectionStats',
            'totalUsers',
            'activeCategories',
            'dates',
            'trendAllowed',
            'trendBlocked',
            'trendFlagged'
        ));
    }
}
