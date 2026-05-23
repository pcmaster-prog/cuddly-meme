<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';

    protected $fillable = [
        'title',
        'description',
        'category',
        'thumbnail_url',
        'is_active',
        'xp_reward'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the modules inside this course
     */
    public function modules()
    {
        return $this->hasMany(Module::class, 'course_id');
    }

    /**
     * Get enrollments for this course
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'course_id');
    }
}
