<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'purchases';

    protected $fillable = [     //not sure can fillable or not sincce userId and Game should use the session
        'p_userId',
        'p_gameName',
        'p_gameId',
        'p_purchaseDate',
        'p_receiptNumber',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'p_userId');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'p_gameId');
    }
}
