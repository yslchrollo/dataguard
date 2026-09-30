<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\SensitiveDataCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = SensitiveDataCategory::withCount('detections')->latest()->get();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:sensitive_data_categories'],
            'description' => ['nullable', 'string'],
            'risk_level' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'keywords_input' => ['nullable', 'string'],
        ]);

        $patterns = [];
        if (!empty($validated['keywords_input'])) {
            $patterns = array_values(array_filter(array_map('trim', explode(',', $validated['keywords_input']))));
        }

        $category = SensitiveDataCategory::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'],
            'risk_level' => $validated['risk_level'],
            'patterns' => $patterns,
            'is_active' => true,
        ]);

        ActivityLog::record(
            'category_created',
            'Policy',
            "Administrator created Sensitive Data Category [{$category->name}].",
            ['category_id' => $category->id, 'code' => $category->code]
        );

        return redirect()->route('categories.index')->with('success', "Sensitive Data Category '{$category->name}' created.");
    }

    public function update(Request $request, SensitiveDataCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'risk_level' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'keywords_input' => ['nullable', 'string'],
        ]);

        $patterns = $category->patterns ?? [];
        if ($request->has('keywords_input')) {
            $patterns = array_values(array_filter(array_map('trim', explode(',', $request->input('keywords_input')))));
        }

        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'risk_level' => $validated['risk_level'],
            'patterns' => $patterns,
        ]);

        ActivityLog::record(
            'category_updated',
            'Policy',
            "Administrator updated Sensitive Data Category [{$category->name}].",
            ['category_id' => $category->id]
        );

        return redirect()->route('categories.index')->with('success', "Category '{$category->name}' updated.");
    }

    public function toggle(SensitiveDataCategory $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        $statusStr = $category->is_active ? 'enabled' : 'disabled';

        ActivityLog::record(
            'category_toggled',
            'Policy',
            "Category [{$category->name}] {$statusStr}.",
            ['category_id' => $category->id, 'is_active' => $category->is_active]
        );

        return back()->with('success', "Category '{$category->name}' is now {$statusStr}.");
    }
}
