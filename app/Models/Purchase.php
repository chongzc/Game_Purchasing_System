<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'purchases';
    protected $primaryKey = 'p_id';

    protected $fillable = [
        'p_userId',
        'p_gameId',
        'p_gameName',
        'p_purchaseDate',
        'p_purchasePrice',
        'p_receiptNumber'
    ];

    protected $casts = [
        'p_purchaseDate' => 'datetime',
        'p_purchasePrice' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'p_userId', 'u_id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'p_gameId', 'g_id');
    }
}
