<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'role',
        'avatar_url',
        'permissions',
        'schedule_config',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
        'schedule_config' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the password for the user.
     * Required since we use custom password_hash field instead of password.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Get the workspace memberships of this user
     */
    public function memberships()
    {
        return $this->hasMany(WorkspaceMember::class, 'user_id');
    }

    /**
     * Get tasks assigned to this user
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    /**
     * Get the LMS courses enrollments of this user
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'user_id');
    }

    /**
     * Get the user's gamification logs ledger
     */
    public function gamificationLedger()
    {
        return $this->hasMany(GamificationLedger::class, 'user_id');
    }

    /**
     * Get the prompts created by the user
     */
    public function prompts()
    {
        return $this->hasMany(Prompt::class, 'creator_id');
    }
}
