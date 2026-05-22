<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = [
        'workspace_id',
        'title',
        'description',
        'area',
        'status',
        'priority',
        'assignee_id',
        'supervisor_id',
        'due_date',
        'evidence_urls',
        'completed_at'
    ];

    protected $casts = [
        'evidence_urls' => 'array',
        'due_date' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Get the user assigned to this task
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Get the supervisor who created/manages this task
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Get the workspace that owns this task
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }
}
