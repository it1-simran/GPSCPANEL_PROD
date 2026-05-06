<?php

namespace App\Http\Controllers;

use App\Models\TestPlan;
use App\Models\TestPlanStep;
use App\Models\Protocol;
use Illuminate\Http\Request;

class TestPlanController extends Controller
{
    public function index()
    {
        $testPlans = TestPlan::withCount('steps')->latest()->get();
        return view('test_plans.index', compact('testPlans'));
    }

    public function create()
    {
        $protocols = Protocol::where('is_active', true)->orderBy('name')->get();
        return view('test_plans.create', compact('protocols'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'protocol_id' => 'required|exists:protocols,id',
            'steps' => 'required|array|min:1',
            'steps.*.step_type' => 'required|string',
            'steps.*.config' => 'required|array',
        ]);

        $testPlan = TestPlan::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'protocol_id' => $validated['protocol_id'],
        ]);

        foreach ($request->input('steps') as $index => $stepData) {
            $testPlan->steps()->create([
                'sequence' => $index + 1,
                'step_type' => $stepData['step_type'],
                'config' => $stepData['config'],
            ]);
        }

        return redirect()->route('admin.test-plans.index')->with('success', 'Test Plan created successfully.');
    }

    public function edit(TestPlan $testPlan)
    {
        $testPlan->load('steps');
        $protocols = Protocol::where('is_active', true)->orderBy('name')->get();
        return view('test_plans.edit', compact('testPlan', 'protocols'));
    }

    public function update(Request $request, TestPlan $testPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'protocol_id' => 'required|exists:protocols,id',
            'steps' => 'required|array|min:1',
            'steps.*.step_type' => 'required|string',
            'steps.*.config' => 'required|array',
        ]);

        $testPlan->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'protocol_id' => $validated['protocol_id'],
        ]);

        $testPlan->steps()->delete();

        foreach ($request->input('steps') as $index => $stepData) {
            $testPlan->steps()->create([
                'sequence' => $index + 1,
                'step_type' => $stepData['step_type'],
                'config' => $stepData['config'],
            ]);
        }

        return redirect()->route('admin.test-plans.index')->with('success', 'Test Plan updated successfully.');
    }

    public function destroy(TestPlan $testPlan)
    {
        $testPlan->delete();
        return redirect()->route('admin.test-plans.index')->with('success', 'Test Plan deleted successfully.');
    }
}
