<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AutomationRule;
use Illuminate\Support\Facades\Auth;

class AutomationController extends Controller
{
    /**
     * Get automation rules list
     */
    public function index(Request $request)
    {
        $workspaceId = $request->header('X-Workspace-Id');
        $rules = AutomationRule::where('workspace_id', $workspaceId)->get();
        return response()->json($rules);
    }

    /**
     * Store an automation rule configuration
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'trigger_config' => 'required|array',
            'action_config' => 'required|array'
        ]);

        $workspaceId = $request->header('X-Workspace-Id');

        $rule = AutomationRule::create([
            'workspace_id' => $workspaceId,
            'name' => $validated['name'],
            'is_active' => true,
            'trigger_config' => $validated['trigger_config'],
            'action_config' => $validated['action_config']
        ]);

        return response()->json($rule, 201);
    }

    /**
     * Toggle active rule status
     */
    public function toggleRule(Request $request, $id)
    {
        $rule = AutomationRule::findOrFail($id);
        $rule->is_active = !$rule->is_active;
        $rule->save();

        return response()->json($rule);
    }
}
