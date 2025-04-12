<?php

namespace Database\Seeders;

use App\Models\Purchase;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('u_role', 'user')->get();
        $games = Game::all();

        $purchases = [
            [
                'game' => 'Elden Ring',
                'buyers' => ['john@example.com', 'mike@example.com']
            ],
            [
                'game' => 'The Last of Us Part II',
                'buyers' => ['jane@example.com', 'john@example.com']
            ],
            [
                'game' => 'Red Dead Redemption 2',
                'buyers' => ['mike@example.com', 'jane@example.com']
            ]
        ];

        foreach ($purchases as $purchase) {
            $game = Game::where('g_title', $purchase['game'])->first();
            
            if ($game) {
                foreach ($purchase['buyers'] as $buyerEmail) {
                    $buyer = User::where('u_email', $buyerEmail)->first();
                    if ($buyer) {
                        Purchase::create([
                            'p_gameName' => $game->g_title,
                            'p_userId' => $buyer->u_id,
                            'p_gameId' => $game->g_id,
                            'p_purchasePrice' => $game->g_price - ($game->g_price * ($game->g_discount / 100)),
                            'p_purchaseDate' => now(),
                            'p_receiptNumber' => 'REC-' . Str::random(10)
                        ]);
                    }
                }
            }
        }
    }
}
