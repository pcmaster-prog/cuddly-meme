<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $table = 'modules';

    protected $fillable = [
        'course_id',
        'title',
        'order_index'
    ];

    /**
     * Get the course that owns this module
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Get lessons belonging to this module
     */
    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'module_id');
    }
}
