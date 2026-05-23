<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationRule extends Model
{
    use HasFactory;

    protected $table = 'automation_rules';

    protected $fillable = [
        'workspace_id',
        'name',
        'is_active',
        'trigger_config',
        'action_config'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'trigger_config' => 'array',
        'action_config' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the workspace that owns this rule
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }
}
