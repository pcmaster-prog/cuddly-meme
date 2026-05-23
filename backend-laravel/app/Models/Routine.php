<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Routine extends Model
{
    use HasFactory;

    protected $table = 'routines';

    protected $fillable = [
        'workspace_id',
        'type',
        'title',
        'days_of_week',
        'checklist_items'
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'checklist_items' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the workspace of this routine template
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }
}
