<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledPost extends Model
{
    use HasFactory;

    protected $table = 'scheduled_posts';

    protected $fillable = [
        'workspace_id',
        'creator_id',
        'social_account_id',
        'status',
        'caption',
        'media_urls',
        'scheduled_for',
        'published_at',
        'analytics'
    ];

    protected $casts = [
        'media_urls' => 'array',
        'analytics' => 'array',
        'scheduled_for' => 'datetime',
        'published_at' => 'datetime'
    ];

    /**
     * Get the social account this post is scheduled for
     */
    public function socialAccount()
    {
        return $this->belongsTo(SocialAccount::class, 'social_account_id');
    }

    /**
     * Get the creator of this scheduled post
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the workspace of this post
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }
}
