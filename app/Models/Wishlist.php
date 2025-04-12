<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;

    protected $table = 'wishlist';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'wl_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'wl_userId',
        'wl_gameId',
        'wl_name',
    ];

    /**
     * Get user's wishlist
     * 
     */
    public function userWishlist()
    {
        return $this->belongsTo(User::class, 'wl_userId', 'u_id');
    }

    /**
     * Get game associated with this wishlist
     * 
     */
    public function gameWishlisted()
    {
        return $this->belongsTo(Game::class, 'wl_gameId', 'g_id');
    }
}
