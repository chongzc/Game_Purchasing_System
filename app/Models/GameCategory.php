<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameCategory extends Model
{
    use HasFactory;

    protected $table = 'game_categories';
    protected $primaryKey = 'gc_id';

    protected $fillable = [ 'gc_category' ];

    public function gamesCategory()
    {
        return $this->belongsToMany(Game::class, 'game_category', 'gc_id', 'gc_gameId');
    }
}
