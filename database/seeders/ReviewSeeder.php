<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            [
                'game' => 'Elden Ring',
                'reviews' => [
                    [
                        'user_email' => 'john@example.com',
                        'text' => 'This game is a masterpiece. The open world design is breathtaking, and the combat is challenging but rewarding.',
                        'rating' => 5
                    ],
                    [
                        'user_email' => 'jane@example.com',
                        'text' => 'Amazing atmosphere and world design. Some boss fights are quite challenging.',
                        'rating' => 4
                    ]
                ]
            ],
            [
                'game' => 'The Last of Us Part II',
                'reviews' => [
                    [
                        'user_email' => 'mike@example.com',
                        'text' => 'A powerful and emotional story with stunning graphics and gameplay.',
                        'rating' => 5
                    ],
                    [
                        'user_email' => 'john@example.com',
                        'text' => 'Incredible narrative and character development. The attention to detail is amazing.',
                        'rating' => 5
                    ]
                ]
            ],
            [
                'game' => 'Red Dead Redemption 2',
                'reviews' => [
                    [
                        'user_email' => 'jane@example.com',
                        'text' => 'One of the most immersive open-world games ever made. The story is unforgettable.',
                        'rating' => 5
                    ],
                    [
                        'user_email' => 'mike@example.com',
                        'text' => 'A true masterpiece of storytelling and world-building.',
                        'rating' => 5
                    ]
                ]
            ]
        ];

        foreach ($reviews as $gameReview) {
            $game = Game::where('g_title', $gameReview['game'])->first();
            
            if ($game) {
                foreach ($gameReview['reviews'] as $review) {
                    $user = User::where('u_email', $review['user_email'])->first();
                    if ($user) {
                        Review::create([
                            'r_userId' => $user->u_id,
                            'r_gameId' => $game->g_id,
                            'r_reviewText' => $review['text'],
                            'r_rating' => $review['rating']
                        ]);
                    }
                }
            }
        }
    }
}
