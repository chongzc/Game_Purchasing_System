<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameCategory extends Model
{
    use HasFactory;

    protected $table = 'game_category';

    protected $fillable = [
        'gc_game_id',
        'gc_category_id',
    ];

    public function games()
    {
        return $this->belongsToMany(Game::class, 'game_category', 'gc_category_id', 'gc_game_id');
    }
}
