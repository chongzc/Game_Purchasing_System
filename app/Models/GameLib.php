<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameLibrary extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'game_lib';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'gl_name',
        'gl_gameId',
        'gl_userId',
        'gl_status',
    ];

    /**
     * Get the user that owns this game library entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'gl_userId');
    }

    /**
     * Get the game associated with this game library entry.
     */
    public function game()
    {
        return $this->belongsTo(Game::class, 'gl_gameId', 'g_id');
    }

    /**
     * Scope a query to only include active games in the library.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('gl_status', 'active');
    }
}
