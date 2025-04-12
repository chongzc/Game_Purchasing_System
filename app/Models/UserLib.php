<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLibrary extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'user_lib';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ul_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ul_name',
        'ul_gameId',
        'ul_userId',
        'ul_status',
    ];

    /**
     * Get the user that owns this game library entry.
     */
    public function userGameLibrary()
    {
        return $this->belongsTo(User::class, 'ul_userId');
    }

    /**
     * Get the game associated with this game library entry.
     */
    public function gameInLibrary()
    {
        return $this->belongsTo(Game::class, 'ul_gameId', 'g_id');
    }

    /**
     * Scope a query to only scope with status of games in the library.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('ul_status', $status);
    }
}
