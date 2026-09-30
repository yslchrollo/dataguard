<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\SecurityPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PolicyController extends Controller
{
    public function index(Request $request)
    {
        $query = SecurityPolicy::with('creator')->latest();

        if ($request->filled('type')) {
            $query->where('policy_type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('policy_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $policies = $query->paginate(10)->withQueryString();

        return view('policies.index', compact('policies'));
    }

    public function create()
    {
        return view('policies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'policy_code' => ['required', 'string', 'max:50', 'unique:security_policies'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'policy_type' => ['required', Rule::in(['sensitive_data_rule', 'destination_check', 'file_type_restriction', 'file_size_limit', 'rate_limit'])],
            'action_on_violation' => ['required', Rule::in(['block', 'flag'])],
            'severity' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'is_active' => ['nullable', 'boolean'],
            'rule_config_text' => ['nullable', 'string'],
        ]);

        $ruleConfig = [];
        if (!empty($validated['rule_config_text'])) {
            $decoded = json_decode($validated['rule_config_text'], true);
            $ruleConfig = is_array($decoded) ? $decoded : ['notes' => $validated['rule_config_text']];
        }

        $policy = SecurityPolicy::create([
            'policy_code' => strtoupper($validated['policy_code']),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'policy_type' => $validated['policy_type'],
            'action_on_violation' => $validated['action_on_violation'],
            'severity' => $validated['severity'],
            'rule_config' => $ruleConfig,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => Auth::id(),
        ]);

        ActivityLog::record(
            'policy_created',
            'Policy',
            "Administrator created DLP Security Policy [{$policy->policy_code}] - {$policy->name}",
            ['policy_id' => $policy->id, 'code' => $policy->policy_code, 'action' => $policy->action_on_violation]
        );

        return redirect()->route('policies.index')->with('success', "Security Policy [{$policy->policy_code}] created successfully.");
    }

    public function edit(SecurityPolicy $policy)
    {
        return view('policies.edit', compact('policy'));
    }

    public function update(Request $request, SecurityPolicy $policy)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'policy_type' => ['required', Rule::in(['sensitive_data_rule', 'destination_check', 'file_type_restriction', 'file_size_limit', 'rate_limit'])],
            'action_on_violation' => ['required', Rule::in(['block', 'flag'])],
            'severity' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'is_active' => ['nullable', 'boolean'],
            'rule_config_text' => ['nullable', 'string'],
        ]);

        $ruleConfig = $policy->rule_config ?? [];
        if ($request->has('rule_config_text')) {
            $decoded = json_decode($request->input('rule_config_text'), true);
            $ruleConfig = is_array($decoded) ? $decoded : ['notes' => $request->input('rule_config_text')];
        }

        $policy->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'policy_type' => $validated['policy_type'],
            'action_on_violation' => $validated['action_on_violation'],
            'severity' => $validated['severity'],
            'rule_config' => $ruleConfig,
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::record(
            'policy_updated',
            'Policy',
            "Administrator updated DLP Policy [{$policy->policy_code}].",
            ['policy_id' => $policy->id, 'code' => $policy->policy_code]
        );

        return redirect()->route('policies.index')->with('success', "Security Policy [{$policy->policy_code}] updated successfully.");
    }

    public function toggle(SecurityPolicy $policy)
    {
        $policy->update(['is_active' => !$policy->is_active]);
        $statusStr = $policy->is_active ? 'enabled' : 'disabled';

        ActivityLog::record(
            'policy_toggled',
            'Policy',
            "Policy [{$policy->policy_code}] was {$statusStr}.",
            ['policy_id' => $policy->id, 'is_active' => $policy->is_active]
        );

        return back()->with('success', "Policy [{$policy->policy_code}] is now {$statusStr}.");
    }

    public function destroy(SecurityPolicy $policy)
    {
        $code = $policy->policy_code;
        $policy->delete();

        ActivityLog::record(
            'policy_deleted',
            'Policy',
            "Administrator deleted Security Policy [{$code}].",
            ['code' => $code]
        );

        return redirect()->route('policies.index')->with('success', "Policy [{$code}] deleted successfully.");
    }
}
