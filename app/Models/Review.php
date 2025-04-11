<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    protected $fillable = [
        'r_userId',
        'r_gameId',
        'r_reviewText',
        'r_rating',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'r_userId');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'r_gameId');
    }
}
