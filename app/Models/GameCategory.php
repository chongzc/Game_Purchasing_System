<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameCategory extends Model
{
    use HasFactory;

    protected $table = 'game_category';

    protected $fillable = [
        'gc_gameId',
        'gc_category',
    ];

    public function games()
    {
        return $this->belongsToMany(Game::class, 'game_category', 'gc_category', 'gc_gameId');
    }
}
