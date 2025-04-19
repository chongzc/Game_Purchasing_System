<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'cart';
    
    //protected $primaryKey = 'c_id';

    protected $fillable = [
        'c_userId',
        'c_gameId',
        'c_price',
        'c_discount',
    ];

    protected $casts = [
        'c_price' => 'decimal:2',
    ];

    /**
     * Get the user who owns the cart item.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'c_userId', 'u_id');
    }

    /**
     * Get the game in the cart.
     */
    public function game()
    {
        return $this->belongsTo(Game::class, 'c_gameId', 'g_id');
    }
}

