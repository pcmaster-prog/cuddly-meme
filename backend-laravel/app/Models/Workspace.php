<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasFactory;

    protected $table = 'workspaces';

    protected $fillable = [
        'name',
        'slug',
        'owner_id'
    ];

    /**
     * Get the workspace owner
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get members of this workspace
     */
    public function members()
    {
        return $this->hasMany(WorkspaceMember::class, 'workspace_id');
    }

    /**
     * Get connected social media accounts for this workspace
     */
    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class, 'workspace_id');
    }

    /**
     * Get posts scheduled within this workspace
     */
    public function scheduledPosts()
    {
        return $this->hasMany(ScheduledPost::class, 'workspace_id');
    }

    /**
     * Get tasks registered in this workspace
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'workspace_id');
    }

    /**
     * Get routine templates in this workspace
     */
    public function routines()
    {
        return $this->hasMany(Routine::class, 'workspace_id');
    }
}
