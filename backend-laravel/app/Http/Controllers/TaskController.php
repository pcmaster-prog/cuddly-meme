<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Routine;
use App\Models\User;
use App\Models\GamificationLedger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TaskController extends Controller
{
    /**
     * Get tasks lists, filtered by area or workspace
     */
    public function index(Request $request)
    {
        $workspaceId = $request->header('X-Workspace-Id');
        
        $query = Task::where('workspace_id', $workspaceId);
        
        if ($request->has('area')) {
            $query->where('area', $request->query('area'));
        }

        if ($request->has('assignee_id')) {
            $query->where('assignee_id', $request->query('assignee_id'));
        }

        $tasks = $query->orderBy('priority', 'desc')->orderBy('created_at', 'desc')->get();

        return response()->json($tasks);
    }

    /**
     * Create a new task (Monday style)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'area' => 'required|in:cajas,produccion,almacen,compras,limpieza,ventas,marketing',
            'priority' => 'required|in:low,medium,high,critical',
            'assignee_id' => 'nullable|uuid|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $workspaceId = $request->header('X-Workspace-Id');

        $task = Task::create([
            'workspace_id' => $workspaceId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'area' => $validated['area'],
            'status' => 'pending',
            'priority' => $validated['priority'],
            'assignee_id' => $validated['assignee_id'] ?? null,
            'supervisor_id' => Auth::id(),
            'due_date' => $validated['due_date'] ?? null,
            'evidence_urls' => []
        ]);

        return response()->json($task, 201);
    }

    /**
     * Update task status or details
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        
        // Authorization check (e.g. users can update their own tasks, or supervisor can manage all)
        if (Auth::user()->role === 'empleado' && $task->assignee_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado para modificar esta tarea.'], 403);
        }

        $validated = $request->validate([
            'status' => 'sometimes|required|in:pending,in_progress,under_review,completed',
            'title' => 'sometimes|required|string',
            'priority' => 'sometimes|required|in:low,medium,high,critical',
            'assignee_id' => 'sometimes|nullable|uuid',
        ]);

        if (isset($validated['status'])) {
            $task->status = $validated['status'];
            
            if ($validated['status'] === 'completed') {
                $task->completed_at = Carbon::now();
                
                // Gamification hook: Award +25 XP to assignee on completion
                if ($task->assignee_id) {
                    $user = User::find($task->assignee_id);
                    if ($user) {
                        $xpAwarded = 25;
                        
                        GamificationLedger::create([
                            'user_id' => $user->id,
                            'award_type' => 'xp',
                            'award_name' => 'Tarea Completada',
                            'value' => $xpAwarded,
                            'reason' => 'Completó tarea operativa: ' . $task->title
                        ]);
                        
                        // Increment in user record
                        $user->increment('xp', $xpAwarded);
                    }
                }
            }
        }

        $task->fill(array_filter($validated, fn($key) => $key !== 'status', ARRAY_FILTER_USE_KEY));
        $task->save();

        return response()->json($task);
    }

    /**
     * Upload photo / video evidence of task completion to Cloudflare R2
     */
    public function uploadEvidence(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,mp4|max:20480', // limit 20MB
        ]);

        $task = Task::findOrFail($id);
        
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            // Cloudflare R2 driver path: file will be uploaded to S3 compatible bucket
            $path = Storage::disk('r2')->putFile('evidence/' . $task->workspace_id, $file, 'public');
            $url = Storage::disk('r2')->url($path);

            $currentEvidences = $task->evidence_urls ?? [];
            $currentEvidences[] = $url;
            
            $task->evidence_urls = $currentEvidences;
            $task->status = 'under_review'; // Change status to under review upon evidence submission
            $task->save();

            return response()->json([
                'message' => 'Evidencia cargada correctamente.',
                'url' => $url,
                'task' => $task
            ]);
        }

        return response()->json(['message' => 'No se adjuntó ningún archivo.'], 400);
    }

    /**
     * Get routine template list
     */
    public function getRoutines(Request $request)
    {
        $workspaceId = $request->header('X-Workspace-Id');
        $routines = Routine::where('workspace_id', $workspaceId)->get();
        return response()->json($routines);
    }

    /**
     * Trigger instantiation of routines (e.g. runs every morning to populate tasks)
     */
    public function triggerRoutines(Request $request)
    {
        $request->validate([
            'routine_id' => 'required|uuid|exists:routines,id',
        ]);

        $routine = Routine::findOrFail($request->input('routine_id'));
        $workspaceId = $request->header('X-Workspace-Id');

        $createdTasks = [];
        
        // Loop through each checklist item in the routine and spawn an individual task
        foreach ($routine->checklist_items as $item) {
            $createdTasks[] = Task::create([
                'workspace_id' => $workspaceId,
                'title' => $routine->title . ': ' . $item,
                'description' => 'Rutina diaria automática instanciada el ' . Carbon::now()->toDateString(),
                'area' => 'limpieza', // or mapped dynamically based on routine type
                'status' => 'pending',
                'priority' => 'medium',
                'evidence_urls' => []
            ]);
        }

        return response()->json([
            'message' => 'Rutina instanciada con éxito.',
            'tasks_created' => count($createdTasks),
            'tasks' => $createdTasks
        ]);
    }
}
