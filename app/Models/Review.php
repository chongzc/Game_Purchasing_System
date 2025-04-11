<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    protected $fillable = [
        'r_user_id',
        'r_game_id',
        'r_review_text',
        'r_rating',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'r_user_id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'r_game_id');
    }
}
