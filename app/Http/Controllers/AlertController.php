<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Alert::with(['transfer.user', 'user', 'assignee'])->latest();

        if ($user->isUser()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('alert_uuid', 'like', "%{$search}%")
                  ->orWhere('alert_type', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $alerts = $query->paginate(10)->withQueryString();

        return view('alerts.index', compact('alerts'));
    }

    public function show(Alert $alert)
    {
        $user = Auth::user();
        if ($user->isUser() && $alert->user_id !== $user->id) {
            abort(403, 'Unauthorized access to alert record.');
        }

        $alert->load(['transfer.detections.category', 'transfer.user', 'assignee', 'investigator']);

        return view('alerts.show', compact('alert'));
    }

    public function update(Request $request, Alert $alert)
    {
        $user = Auth::user();
        if ($user->isUser()) {
            abort(403, 'Regular users cannot modify incident investigation records.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['open', 'under_review', 'resolved', 'dismissed'])],
            'action_taken' => ['required', 'string', 'max:255'],
            'investigation_notes' => ['required', 'string', 'max:2000'],
        ]);

        $isResolving = in_array($validated['status'], ['resolved', 'dismissed']);

        $alert->update([
            'status' => $validated['status'],
            'action_taken' => $validated['action_taken'],
            'investigation_notes' => $validated['investigation_notes'],
            'investigated_by' => $user->id,
            'resolved_at' => $isResolving ? now() : null,
        ]);

        ActivityLog::record(
            'alert_investigated',
            'Alert',
            "Security Analyst/Admin {$user->name} updated alert [{$alert->alert_uuid}]. Status: " . strtoupper($validated['status']) . ". Action: {$validated['action_taken']}",
            [
                'alert_id' => $alert->id,
                'alert_uuid' => $alert->alert_uuid,
                'status' => $validated['status'],
                'action_taken' => $validated['action_taken'],
            ],
            $user
        );

        return redirect()->route('alerts.show', $alert->id)->with('success', "Investigation recorded successfully for Alert [{$alert->alert_uuid}].");
    }
}
