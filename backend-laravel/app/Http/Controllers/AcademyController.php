<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\GamificationLedger;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AcademyController extends Controller
{
    /**
     * Get active courses catalog
     */
    public function getCourses(Request $request)
    {
        $courses = Course::where('is_active', true)
            ->withCount('modules')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($courses);
    }

    /**
     * Show course syllabus (modules and lessons)
     */
    public function showCourse($id)
    {
        $course = Course::findOrFail($id);
        
        $course->load(['modules' => function ($query) {
            $query->orderBy('order_index', 'asc')->with(['lessons' => function ($q) {
                $q->orderBy('order_index', 'asc');
            }]);
        }]);

        // Check if current user is enrolled
        $enrollment = Enrollment::where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->first();

        return response()->json([
            'course' => $course,
            'modules' => $course->modules,
            'enrollment' => $enrollment
        ]);
    }

    /**
     * Enroll in a course
     */
    public function enroll($id)
    {
        $course = Course::findOrFail($id);
        
        $enrollment = Enrollment::firstOrCreate([
            'user_id' => Auth::id(),
            'course_id' => $course->id
        ], [
            'progress_percentage' => 0,
            'current_xp' => 0,
            'completed' => false
        ]);

        return response()->json([
            'message' => 'Inscripción completada.',
            'enrollment' => $enrollment
        ]);
    }

    /**
     * Complete a lesson and increment XP / progress
     */
    public function completeLesson(Request $request, $id)
    {
        $lesson = Lesson::findOrFail($id);
        $module = $lesson->module;
        $course = $module->course;
        
        $userId = Auth::id();

        // Ensure user is enrolled
        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $course->id)
            ->firstOrFail();

        // Calculate progress percentage
        // Total lessons in the course
        $totalLessons = Lesson::whereIn('module_id', $course->modules->pluck('id'))->count();
        
        // Simulating marking the lesson completed: In production, a many-to-many completed_lessons table exists.
        // We will increment the progress percentage based on lesson weight
        $progressStep = $totalLessons > 0 ? (int)(100 / $totalLessons) : 100;
        $newProgress = min(100, $enrollment->progress_percentage + $progressStep);
        
        $enrollment->progress_percentage = $newProgress;
        $enrollment->save();

        // Award XP for completing lesson (+15 XP)
        $xpAwarded = 15;
        $enrollment->increment('current_xp', $xpAwarded);
        
        // Award to global user XP profile
        $user = User::find($userId);
        $user->increment('xp', $xpAwarded);

        // Record in ledger
        GamificationLedger::create([
            'user_id' => $userId,
            'award_type' => 'xp',
            'award_name' => 'Lección Finalizada',
            'value' => $xpAwarded,
            'reason' => "Completó lección: '{$lesson->title}' en '{$course->title}'"
        ]);

        // If progress is 100%, trigger course completion
        if ($newProgress === 100 && !$enrollment->completed) {
            $this->triggerCourseCompletion($enrollment, $course, $user);
        }

        return response()->json([
            'message' => 'Lección marcada como completada.',
            'progress' => $newProgress,
            'xp_earned' => $xpAwarded,
            'enrollment' => $enrollment
        ]);
    }

    /**
     * Evaluate course final quiz and award completion badge
     */
    public function submitQuiz(Request $request, $id)
    {
        $lesson = Lesson::findOrFail($id); // Assuming quiz is a type of lesson or attached to it
        $request->validate([
            'answers' => 'required|array',
        ]);

        // Mock quiz correction (In production, load quiz questions and check correctness)
        $score = 85; // 85% correct
        $passed = $score >= 70; // passing grade threshold is 70

        if ($passed) {
            return response()->json([
                'passed' => true,
                'score' => $score,
                'message' => '¡Felicidades! Aprobaste la evaluación.'
            ]);
        }

        return response()->json([
            'passed' => false,
            'score' => $score,
            'message' => 'Tu puntuación no alcanzó el mínimo requerido. Inténtalo de nuevo.'
        ], 422);
    }

    /**
     * Fetch user badges ledger
     */
    public function getUserBadges($id)
    {
        $badges = GamificationLedger::where('user_id', $id)
            ->where('award_type', 'badge')
            ->orderBy('awarded_at', 'desc')
            ->get();
            
        return response()->json($badges);
    }

    /**
     * Helper: handle course completion rewards
     */
    private function triggerCourseCompletion(Enrollment $enrollment, Course $course, User $user)
    {
        $enrollment->completed = true;
        $enrollment->completed_at = Carbon::now();
        $enrollment->save();

        // Award Course Reward XP
        $courseXp = $course->xp_reward ?? 100;
        $user->increment('xp', $courseXp);

        // Log Course Completion XP
        GamificationLedger::create([
            'user_id' => $user->id,
            'award_type' => 'xp',
            'award_name' => 'Curso Finalizado',
            'value' => $courseXp,
            'reason' => "Completó satisfactoriamente todo el curso: '{$course->title}'"
        ]);

        // Award a Badge
        GamificationLedger::create([
            'user_id' => $user->id,
            'award_type' => 'badge',
            'award_name' => 'Graduado: ' . $course->title,
            'value' => 0,
            'reason' => 'Insignia otorgada por completar el entrenamiento oficial en el hub.'
        ]);
    }
}
