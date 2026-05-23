<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $table = 'lessons';

    protected $fillable = [
        'module_id',
        'title',
        'content_type',
        'video_url',
        'attachment_url',
        'duration_minutes',
        'order_index'
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the module that owns this lesson
     */
    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
}
