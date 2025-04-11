<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'purchases';

    protected $fillable = [
        'p_user_id',
        'p_gameName',
        'p_gameId',
        'p_purchaseDate',
        'p_receiptNumber',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'p_user_id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'p_gameId');
    }
}
