<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    use HasFactory;

    protected $table = 'prompts';

    protected $fillable = [
        'creator_id',
        'title',
        'prompt_text',
        'category',
        'version',
        'is_favorite'
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
        'version' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the creator of this prompt
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
