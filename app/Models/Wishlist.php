<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;

    protected $table = 'wishlist';

    protected $fillable = [
        'wl_userId',
        'wl_gameId',
        'wl_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'wl_userId');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'wl_gameId');
    }
}
