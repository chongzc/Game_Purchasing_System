<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\UserLib;
use App\Models\User;
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

        $flashSales = Game::with('developer')
            ->where('g_discount', '>', 0)
            ->orderBy('g_discount', 'desc')
            ->take(40)
            ->paginate(4)
            ->map(function ($game) {
                return [
                    'id' => $game->g_id,
                    'name' => $game->g_title,
                    'originalPrice' => $game->g_price,
                    'price' => $game->g_price - ($game->g_price * ($game->g_discount / 100)),
                    'rating' => $game->g_overallRate,
                    'image' => $game->g_mainImage
                ];
            });

        $bestSelling = Game::with('developer')
            ->orderBy('g_downloadCount', 'desc')
            ->take(40)
            ->paginate(4)
            ->map(function ($game) {
                return [
                    'id' => $game->g_id,
                    'name' => $game->g_title,
                    'originalPrice' => $game->g_price,
                    'price' => $game->g_price - ($game->g_price * ($game->g_discount / 100)),
                    'rating' => $game->g_overallRate,
                    'image' => $game->g_mainImage
                ];
            });

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
        $game = Game::with('developer', 'purchasedUsers', 'wishlistedUsers', 'libraryUsers')
            ->where('g_id', $id)
            ->firstOrFail();

        return view('game-details', compact('game'));
    }

    /**
     * Display the user's game library.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function library(Request $request)
    {
        $userLibrary = UserLib::with('game')
            ->where('ul_userId', auth()->id())
            ->get()
            ->map(function ($lib) {
                return [
                    'id' => $lib->game->g_id,
                    'title' => $lib->game->g_title,
                    'status' => $lib->ul_status,
                    'image' => $lib->game->g_mainImage,
                ];
            });

        return response()->json($userLibrary);
    }
}
