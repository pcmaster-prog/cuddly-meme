<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SocialAccount;
use App\Models\ScheduledPost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class SocialHubController extends Controller
{
    /**
     * Get list of connected social accounts
     */
    public function getAccounts(Request $request)
    {
        $workspaceId = $request->header('X-Workspace-Id');
        $accounts = SocialAccount::where('workspace_id', $workspaceId)->get();
        return response()->json($accounts);
    }

    /**
     * Get posts queue / schedule list
     */
    public function getPosts(Request $request)
    {
        $workspaceId = $request->header('X-Workspace-Id');
        
        $posts = ScheduledPost::where('workspace_id', $workspaceId)
            ->orderBy('scheduled_for', 'asc')
            ->get();
            
        return response()->json($posts);
    }

    /**
     * Store and schedule a post draft
     */
    public function storePost(Request $request)
    {
        $validated = $request->validate([
            'social_account_id' => 'required|uuid|exists:social_accounts,id',
            'caption' => 'nullable|string',
            'media' => 'nullable|file|mimes:jpeg,png,jpg,mp4|max:51200', // max 50MB
            'scheduled_for' => 'required|date|after:now',
        ]);

        $workspaceId = $request->header('X-Workspace-Id');
        $mediaUrls = [];

        // Upload post attachment to Cloudflare R2
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $path = Storage::disk('r2')->putFile('posts/' . $workspaceId, $file, 'public');
            $mediaUrls[] = Storage::disk('r2')->url($path);
        }

        $post = ScheduledPost::create([
            'workspace_id' => $workspaceId,
            'creator_id' => Auth::id(),
            'social_account_id' => $validated['social_account_id'],
            'status' => 'pending', // Awaiting supervisor approval
            'caption' => $validated['caption'] ?? '',
            'media_urls' => $mediaUrls,
            'scheduled_for' => Carbon::parse($validated['scheduled_for']),
            'analytics' => []
        ]);

        return response()->json($post, 201);
    }

    /**
     * Approve a pending post (Supervisor/Admin only)
     */
    public function approvePost(Request $request, $id)
    {
        $post = ScheduledPost::findOrFail($id);
        
        $post->status = 'approved';
        $post->save();

        // Optional: Trigger background scheduling cron to pick it up
        
        return response()->json([
            'message' => 'Post aprobado satisfactoriamente.',
            'post' => $post
        ]);
    }

    /**
     * Dispatch an asynchronous video rendering job to Redis queue for Node.js worker
     */
    public function dispatchRenderJob(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'workflow' => 'required|string',
            'scenes' => 'required|array',
            'voice_engine' => 'nullable|string',
            'subtitles' => 'boolean'
        ]);

        $jobId = 'render_' . uniqid();
        $jobPayload = array_merge($validated, [
            'job_id' => $jobId,
            'workspace_id' => $request->header('X-Workspace-Id'),
            'creator_id' => Auth::id(),
            'status' => 'queued',
            'timestamp' => Carbon::now()->timestamp
        ]);

        // Push to Redis queue for the Node.js FFmpeg workers to process
        Redis::rpush('video_render_queue', json_encode($jobPayload));
        
        // Save initial job metadata in cache / db for status tracking
        Redis::set('job:status:' . $jobId, json_encode([
            'id' => $jobId,
            'title' => $validated['title'],
            'workflow' => $validated['workflow'],
            'progress' => 0,
            'status' => 'queued'
        ]), 'EX', 3600); // 1 hour TTL

        return response()->json([
            'jobId' => $jobId,
            'status' => 'queued',
            'message' => 'Render de video encolado correctamente en los workers de FFmpeg.'
        ]);
    }

    /**
     * Check video render job progress (polled or updated via web sockets)
     */
    public function getRenderJobStatus($jobId)
    {
        $jobStatus = Redis::get('job:status:' . $jobId);
        
        if (!$jobStatus) {
            return response()->json(['message' => 'Trabajo no encontrado o ya expirado.'], 404);
        }

        return response()->json(json_decode($jobStatus));
    }

    /**
     * Get Central Inbox messages
     */
    public function getInbox(Request $request)
    {
        // Mock connection retrieval. In production, this pulls DMs from Meta Graph API & TikTok Developer Webhooks
        return response()->json([
            [
                'id' => 'msg_01',
                'platform' => 'instagram',
                'sender' => '@pasteleria_fans',
                'message' => 'Hola, ¿cuándo inicia el nuevo curso de repostería?',
                'timestamp' => Carbon::now()->subMinutes(15)->toIso8601String(),
                'read' => false
            ],
            [
                'id' => 'msg_02',
                'platform' => 'tiktok',
                'sender' => 'alex_reposteria',
                'message' => '¡Me encantó el video del tip sobre el bizcocho!',
                'timestamp' => Carbon::now()->subHour()->toIso8601String(),
                'read' => true
            ]
        ]);
    }

    /**
     * Reply to a comment or DM via official Social API
     */
    public function sendReply(Request $request)
    {
        $validated = $request->validate([
            'message_id' => 'required|string',
            'reply_text' => 'required|string',
        ]);

        // Integration logic: Post reply to platform API (mocked)
        // Meta Graph / TikTok Graph endpoints are invoked here using official SDKs
        
        return response()->json([
            'success' => true,
            'message' => 'Respuesta enviada a la API oficial con éxito.'
        ]);
    }
}
