<?php

namespace Database\Seeders;

use App\Models\Wishlist;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Seeder;

class WishlistSeeder extends Seeder
{
    public function run(): void
    {
        $wishlists = [
            [
                'user' => 'john@example.com',
                'games' => ['Bloodborne', 'Grand Theft Auto V']
            ],
            [
                'user' => 'jane@example.com',
                'games' => ['Elden Ring', 'Bloodborne']
            ],
            [
                'user' => 'mike@example.com',
                'games' => ['The Last of Us Part II', 'Grand Theft Auto V']
            ]
        ];

        foreach ($wishlists as $wishlist) {
            $user = User::where('u_email', $wishlist['user'])->first();
            
            if ($user) {
                foreach ($wishlist['games'] as $gameTitle) {
                    $game = Game::where('g_title', $gameTitle)->first();
                    if ($game) {
                        Wishlist::create([
                            'wl_name' => $game->g_title,
                            'wl_gameId' => $game->g_id,
                            'wl_userId' => $user->u_id
                        ]);
                    }
                }
            }
        }
    }
}
