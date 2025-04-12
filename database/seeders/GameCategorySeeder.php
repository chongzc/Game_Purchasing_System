<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameCategory;
use Illuminate\Database\Seeder;

class GameCategorySeeder extends Seeder
{
    public function run(): void
    {
        $gamesToCategories = [
            'Elden Ring' => ['Action', 'RPG'],
            'The Last of Us Part II' => ['Action', 'Adventure'],
            'Red Dead Redemption 2' => ['Action', 'Adventure'],
            'Bloodborne' => ['Action', 'RPG'],
            'Grand Theft Auto V' => ['Action', 'Adventure']
        ];

        foreach ($gamesToCategories as $gameTitle => $categories) {
            $game = Game::where('g_title', $gameTitle)->first();
            
            if ($game) {
                foreach ($categories as $category) {
                    GameCategory::create([
                        'gc_gameName' => $game->g_title,
                        'gc_gameId' => $game->g_id,
                        'gc_category' => $category
                    ]);
                }
            }
        }
    }
}
