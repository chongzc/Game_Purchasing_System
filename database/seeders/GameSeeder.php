<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        // Get developer IDs
        $naughtyDogDev = User::where('u_email', 'naughtydog@dev.com')->first()->u_id;
        $fromSoftwareDev = User::where('u_email', 'fromsoftware@dev.com')->first()->u_id;
        $rockstarDev = User::where('u_email', 'rockstar@dev.com')->first()->u_id;

        $games = [
            [
                'g_title' => 'Elden Ring',
                'g_description' => 'THE NEW FANTASY ACTION RPG. Rise, Tarnished, and be guided by grace to brandish the power of the Elden Ring and become an Elden Lord in the Lands Between.',
                'g_price' => 59.99,
                'g_discount' => 0.00,
                'g_developerId' => $fromSoftwareDev,
                'g_status' => 'verified',
                'g_downloadCount' => 1500000,
                'g_mainImage' => '/images/placeholder.jpg',
                'g_overallRate' => 4.9,
                'g_language' => 'English, Japanese'
            ],
            [
                'g_title' => 'The Last of Us Part II',
                'g_description' => 'Five years after their dangerous journey across the post-pandemic United States, Ellie and Joel have settled down in Jackson, Wyoming.',
                'g_price' => 49.99,
                'g_discount' => 20.00,
                'g_developerId' => $naughtyDogDev,
                'g_status' => 'verified',
                'g_downloadCount' => 1200000,
                'g_mainImage' => '/images/placeholder.jpg',
                'g_overallRate' => 4.8,
                'g_language' => 'Multiple Languages'
            ],
            [
                'g_title' => 'Red Dead Redemption 2',
                'g_description' => 'Winner of over 175 Game of the Year Awards and recipient of over 250 perfect scores, Red Dead Redemption 2 is an epic tale of honor and loyalty at the dawn of the modern age.',
                'g_price' => 59.99,
                'g_discount' => 33.00,
                'g_developerId' => $rockstarDev,
                'g_status' => 'verified',
                'g_downloadCount' => 2000000,
                'g_mainImage' => '/images/placeholder.jpg',
                'g_overallRate' => 4.9,
                'g_language' => 'Multiple Languages'
            ],
            [
                'g_title' => 'Bloodborne',
                'g_description' => 'Hunt your nightmares as you search for answers in the ancient city of Yharnam, now cursed with a strange endemic illness spreading through the streets like wildfire.',
                'g_price' => 39.99,
                'g_discount' => 0.00,
                'g_developerId' => $fromSoftwareDev,
                'g_status' => 'verified',
                'g_downloadCount' => 800000,
                'g_mainImage' => '/images/placeholder.jpg',
                'g_overallRate' => 4.7,
                'g_language' => 'English, Japanese'
            ],
            [
                'g_title' => 'Grand Theft Auto V',
                'g_description' => 'Grand Theft Auto V for PC offers players the option to explore the award-winning world of Los Santos and Blaine County in resolutions of up to 4k and beyond.',
                'g_price' => 29.99,
                'g_discount' => 50.00,
                'g_developerId' => $rockstarDev,
                'g_status' => 'verified',
                'g_downloadCount' => 5000000,
                'g_mainImage' => '/images/placeholder.jpg',
                'g_overallRate' => 4.8,
                'g_language' => 'Multiple Languages'
            ]
        ];

        foreach ($games as $game) {
            Game::create($game);
        }
    }
}
