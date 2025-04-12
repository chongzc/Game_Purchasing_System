<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    protected $primaryKey = 'r_id';

    protected $fillable = [
        'r_userId',
        'r_gameId',
        'r_reviewText',
        'r_rating',
    ];

    /**
     * Get the user that owns this review.
     */
    public function userReview()
    {
        return $this->belongsTo(User::class, 'r_userId', 'u_id');
    }

    /**
     * Get the game associated with this review.
     *
     */
    public function gameReviewed()
    {
        return $this->belongsTo(Game::class, 'r_gameId', 'g_id');
    }
}
