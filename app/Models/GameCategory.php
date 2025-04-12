<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameCategory extends Model
{
    use HasFactory;

    protected $table = 'game_categories';

    protected $fillable = [
        'gc_gameName',
        'gc_gameId',
        'gc_category'
    ];

    public function games()
    {
        return $this->belongsToMany(Game::class, 'game_categories', 'gc_category_id', 'gc_game_id');
    }
}
