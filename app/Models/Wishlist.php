<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;

    protected $table = 'wishlist';
    protected $primaryKey = 'wl_id';

    protected $fillable = [
        'wl_userId',
        'wl_gameId',
        'wl_name'
    ];

    /**
     * Get the game associated with the wishlist item.
     */
    public function game()
    {
        return $this->belongsTo(Game::class, 'wl_gameId', 'g_id');
    }

    /**
     * Get the user who wishlisted the game.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'wl_userId', 'u_id');
    }
}
