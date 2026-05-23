<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkspaceMember extends Model
{
    use HasFactory;

    protected $table = 'workspace_members';

    protected $fillable = [
        'workspace_id',
        'user_id',
        'role'
    ];

    // Only joined_at exists, no default updated_at/created_at columns
    public $timestamps = false;

    /**
     * Get the workspace linked
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    /**
     * Get the user linked
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
