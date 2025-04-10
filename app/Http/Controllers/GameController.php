<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Display the game store home page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // In a real application, this data would come from a database
        $flashSales = [
            [
                'id' => 1,
                'name' => 'God of War Ragnarök',
                'originalPrice' => 69.99,
                'price' => 49.99,
                'discount' => 29,
                'rating' => 5,
                'image' => 'placeholder.jpg'
            ],
            [
                'id' => 2,
                'name' => 'Assassin\'s Creed Valhalla',
                'originalPrice' => 59.99,
                'price' => 29.99,
                'discount' => 50,
                'rating' => 4,
                'image' => 'placeholder.jpg'
            ],
            [
                'id' => 3,
                'name' => 'Resident Evil 4 Remake',
                'originalPrice' => 59.99,
                'price' => 39.99,
                'discount' => 33,
                'rating' => 5,
                'image' => 'placeholder.jpg'
            ],
            [
                'id' => 4,
                'name' => 'Spider-Man 2',
                'originalPrice' => 69.99,
                'price' => 49.99,
                'discount' => 29,
                'rating' => 4.5,
                'image' => 'placeholder.jpg'
            ]
        ];

        $bestSelling = [
            [
                'id' => 1,
                'name' => 'Cyberpunk 2077',
                'originalPrice' => 59.99,
                'price' => 39.99,
                'rating' => 4.5,
                'image' => 'placeholder.jpg'
            ],
            [
                'id' => 2,
                'name' => 'Elden Ring',
                'originalPrice' => 69.99,
                'price' => 59.99,
                'rating' => 5,
                'image' => 'placeholder.jpg'
            ],
            [
                'id' => 3,
                'name' => 'FIFA 23',
                'originalPrice' => 59.99,
                'price' => 44.99,
                'rating' => 4,
                'image' => 'placeholder.jpg'
            ],
            [
                'id' => 4,
                'name' => 'Minecraft',
                'originalPrice' => 29.99,
                'price' => 24.99,
                'rating' => 5,
                'image' => 'placeholder.jpg'
            ]
        ];

        $categories = [
            ['id' => 1, 'name' => 'Action', 'icon' => 'bx-run'],
            ['id' => 2, 'name' => 'Adventure', 'icon' => 'bx-map'],
            ['id' => 3, 'name' => 'RPG', 'icon' => 'bx-diamond'],
            ['id' => 4, 'name' => 'Strategy', 'icon' => 'bx-chess'],
            ['id' => 5, 'name' => 'Sports', 'icon' => 'bx-baseball'],
            ['id' => 6, 'name' => 'Simulation', 'icon' => 'bx-city']
        ];

        $countdown = [
            'Days' => '03',
            'Hours' => '23',
            'Min' => '19',
            'Sec' => '56'
        ];

        return view('game-store', compact('flashSales', 'bestSelling', 'categories', 'countdown'));
    }

    /**
     * Show the details for a specific game.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // In a real application, this would come from a database query
        $game = [
            'id' => $id,
            'name' => 'Game ' . $id,
            'description' => 'This is the description for game ' . $id,
            'price' => 49.99,
            'rating' => 4.5,
            'developer' => 'Game Studio',
            'releaseDate' => '2023-01-01',
            'genres' => ['Action', 'Adventure'],
            'image' => 'placeholder.jpg'
        ];

        return view('game-details', compact('game'));
    }
} 
