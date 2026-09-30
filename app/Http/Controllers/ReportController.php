<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\SensitiveDataCategory;
use App\Models\Transfer;
use App\Models\TransferDetection;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $transfersQuery = Transfer::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        $totalTransfers = (clone $transfersQuery)->count();
        $allowedTransfers = (clone $transfersQuery)->where('decision', 'allowed')->count();
        $blockedTransfers = (clone $transfersQuery)->where('decision', 'blocked')->count();
        $flaggedTransfers = (clone $transfersQuery)->where('decision', 'flagged')->count();

        // Violations count
        $policyViolations = TransferDetection::whereHas('transfer', function($q) use ($startDate, $endDate) {
            $q->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
        })->where('detection_technique', 'policy_violation')->count();

        $sensitiveDetections = TransferDetection::whereHas('transfer', function($q) use ($startDate, $endDate) {
            $q->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
        })->where('detection_technique', 'sensitive_data')->count();

        $alertsGenerated = Alert::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)->count();

        // Technique breakdown
        $techniqueStats = TransferDetection::whereHas('transfer', function($q) use ($startDate, $endDate) {
            $q->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
        })->selectRaw('detection_technique, count(*) as count')
          ->groupBy('detection_technique')
          ->pluck('count', 'detection_technique')
          ->toArray();

        // Top destinations
        $topDestinations = (clone $transfersQuery)
            ->selectRaw('destination, destination_type, count(*) as count, sum(case when decision = "blocked" then 1 else 0 end) as blocked_count')
            ->groupBy('destination', 'destination_type')
            ->orderByDesc('count')
            ->take(8)
            ->get();

        // Top users by transfers
        $topUsers = (clone $transfersQuery)
            ->with('user')
            ->selectRaw('user_id, count(*) as transfer_count, sum(case when decision = "blocked" then 1 else 0 end) as blocked_count')
            ->groupBy('user_id')
            ->orderByDesc('transfer_count')
            ->take(6)
            ->get();

        // Violation log samples
        $recentViolations = (clone $transfersQuery)
            ->whereIn('decision', ['blocked', 'flagged'])
            ->with(['user', 'detections'])
            ->latest()
            ->take(10)
            ->get();

        return view('reports.index', compact(
            'startDate',
            'endDate',
            'totalTransfers',
            'allowedTransfers',
            'blockedTransfers',
            'flaggedTransfers',
            'policyViolations',
            'sensitiveDetections',
            'alertsGenerated',
            'techniqueStats',
            'topDestinations',
            'topUsers',
            'recentViolations'
        ));
    }
}
