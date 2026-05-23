<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamificationLedger extends Model
{
    use HasFactory;

    protected $table = 'gamification_ledger';

    protected $fillable = [
        'user_id',
        'award_type',
        'award_name',
        'value',
        'reason'
    ];

    protected $casts = [
        'value' => 'integer',
        'awarded_at' => 'datetime',
    ];

    // Disable default timestamps (only use awarded_at)
    public $timestamps = false;

    /**
     * Get the user who earned this award
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
