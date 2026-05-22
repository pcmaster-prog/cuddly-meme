<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialHubController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AcademyController;
use App\Http\Controllers\PromptController;
use App\Http\Controllers\AutomationController;

/*
|--------------------------------------------------------------------------
| API Routes - DECORARTE MEDIA HUB
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    
    // 1. PUBLIC AUTHENTICATION ROUTES
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // PROTECTED ROUTES (Requires Sanctum token)
    Route::middleware('auth:sanctum')->group(function () {
        
        // 2. USER PROFILE & SECURITY
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/2fa/enable', [AuthController::class, 'enable2FA']);
        Route::post('/2fa/verify', [AuthController::class, 'verify2FA']);

        // 3. SOCIAL MEDIA MANAGER (OAuth and post management)
        Route::prefix('socials')->group(function () {
            Route::get('/accounts', [SocialHubController::class, 'getAccounts']);
            Route::post('/accounts/connect/{platform}', [SocialHubController::class, 'connectPlatform']);
            Route::get('/calendar', [SocialHubController::class, 'getCalendar']);
            Route::get('/posts', [SocialHubController::class, 'getPosts']);
            Route::post('/posts', [SocialHubController::class, 'storePost']);
            Route::post('/posts/{id}/approve', [SocialHubController::class, 'approvePost'])
                ->middleware('can:approve-posts'); // Only Admin/Supervisor
            Route::get('/inbox/central', [SocialHubController::class, 'getInbox']);
            Route::post('/inbox/reply', [SocialHubController::class, 'sendReply']);
            Route::get('/analytics/summary', [SocialHubController::class, 'getAnalytics']);
        });

        // 4. TASK & ROUTINE BOARD (Monday-style)
        Route::prefix('tasks')->group(function () {
            Route::get('/', [TaskController::class, 'index']);
            Route::post('/', [TaskController::class, 'store']);
            Route::put('/{id}', [TaskController::class, 'update']);
            Route::post('/{id}/evidence', [TaskController::class, 'uploadEvidence']);
            Route::get('/routines', [TaskController::class, 'getRoutines']);
            Route::post('/routines/trigger', [TaskController::class, 'triggerRoutines'])
                ->middleware('can:manage-tasks');
        });

        // 5. LMS ACADEMIA
        Route::prefix('academy')->group(function () {
            Route::get('/courses', [AcademyController::class, 'getCourses']);
            Route::get('/courses/{id}', [AcademyController::class, 'showCourse']);
            Route::post('/courses/{id}/enroll', [AcademyController::class, 'enroll']);
            Route::post('/lessons/{id}/complete', [AcademyController::class, 'completeLesson']);
            Route::post('/lessons/{id}/quiz/submit', [AcademyController::class, 'submitQuiz']);
            Route::get('/users/{id}/badges', [AcademyController::class, 'getUserBadges']);
        });

        // 6. PROMPT LIBRARY
        Route::prefix('prompts')->group(function () {
            Route::get('/', [PromptController::class, 'index']);
            Route::post('/', [PromptController::class, 'store']);
            Route::post('/generate', [PromptController::class, 'generateAI']);
        });

        // 7. AUTOMATION ENGINE (If-Then conds)
        Route::prefix('automations')->group(function () {
            Route::get('/rules', [AutomationController::class, 'index']);
            Route::post('/rules', [AutomationController::class, 'store']);
            Route::post('/rules/{id}/toggle', [AutomationController::class, 'toggleRule']);
        });

        // 8. PRODUCTION RENDER QUEUE CONTROLLER
        Route::prefix('production')->group(function () {
            Route::post('/ai/video-render', [SocialHubController::class, 'dispatchRenderJob']);
            Route::get('/jobs/{job_id}/status', [SocialHubController::class, 'getRenderJobStatus']);
        });
    });
});
