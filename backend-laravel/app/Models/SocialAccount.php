<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    use HasFactory;

    protected $table = 'social_accounts';

    protected $fillable = [
        'workspace_id',
        'platform',
        'platform_user_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'token_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the workspace that connects this social account
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    /**
     * Get the posts scheduled for this social account
     */
    public function scheduledPosts()
    {
        return $this->hasMany(ScheduledPost::class, 'social_account_id');
    }
}
