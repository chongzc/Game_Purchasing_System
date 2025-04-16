<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameCategory;
use App\Models\UserLib;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    public function create()
    {
        return view('games.create');
    }

    /**
     * Store a newly created game in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            Log::info('Game creation request:', $request->all());
            
            // Validate request
            $validated = $request->validate([
                'g_title' => 'required|string|max:255',
                'g_description' => 'required|string',
                'g_price' => 'required|numeric|min:0',
                'g_discount' => 'nullable|numeric|min:0|max:100',
                'g_status' => 'nullable|string',
                'g_language' => 'required|string',
                'g_category' => 'nullable|string',
                'g_developerId' => 'required|numeric',
                'categories' => 'required|array',
                'g_mainImage' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'g_exImg1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'g_exImg2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'g_exImg3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            
            // Handle file uploads
            $mainImagePath = null;
            $exImg1Path = null;
            $exImg2Path = null;
            $exImg3Path = null;
            
            if ($request->hasFile('g_mainImage')) {
                $mainImagePath = $request->file('g_mainImage')->store('games', 'public');
            }
            
            if ($request->hasFile('g_exImg1')) {
                $exImg1Path = $request->file('g_exImg1')->store('games', 'public');
            }
            
            if ($request->hasFile('g_exImg2')) {
                $exImg2Path = $request->file('g_exImg2')->store('games', 'public');
            }
            
            if ($request->hasFile('g_exImg3')) {
                $exImg3Path = $request->file('g_exImg3')->store('games', 'public');
            }
            
            // Create new game
            $game = Game::create([
                'g_title' => $request->g_title,
                'g_description' => $request->g_description,
                'g_price' => $request->g_price,
                'g_discount' => $request->g_discount ?? 0,
                'g_status' => $request->g_status ?? 'pending',
                'g_language' => $request->g_language,
                'g_category' => $request->g_category,
                'g_mainImage' => $mainImagePath,
                'g_exImg1' => $exImg1Path,
                'g_exImg2' => $exImg2Path,
                'g_exImg3' => $exImg3Path,
                'g_developerId' => $request->g_developerId ?? auth()->id(),
                'g_downloadCount' => 0,
                'g_overallRate' => 0,
            ]);
            
            // Make sure game is created and has a valid ID
            if ($game && $game->g_id) {
                // Map and add categories
                if (isset($request->categories) && is_array($request->categories)) {
                    foreach ($request->categories as $categoryId) {
                        // Map category ID to category name
                        $categoryName = '';
                        switch ($categoryId) {
                            case 1: $categoryName = 'Action'; break;
                            case 2: $categoryName = 'Adventure'; break;
                            case 3: $categoryName = 'RPG'; break;
                            case 4: $categoryName = 'Strategy'; break;
                            case 5: $categoryName = 'Sports'; break;
                            case 6: $categoryName = 'Racing'; break;
                            case 7: $categoryName = 'Simulation'; break;
                            case 8: $categoryName = 'Puzzle'; break;
                            case 9: $categoryName = 'Platformer'; break;
                            case 10: $categoryName = 'Fighting'; break;
                            case 11: $categoryName = 'Shooter'; break;
                            case 12: $categoryName = 'Horror'; break;
                            case 13: $categoryName = 'Casual'; break;
                            case 14: $categoryName = 'Indie'; break;
                            case 15: $categoryName = 'MMO'; break;
                            default: $categoryName = 'Other'; break;
                        }
                        
                        // Log the game ID to debug
                        Log::info('Creating category with game ID: ' . $game->g_id);
                        
                        GameCategory::create([
                            'gc_gameId' => $game->g_id,
                            'gc_gameName' => $game->g_title,
                            'gc_category' => $categoryName
                        ]);
                    }
                }
            } else {
                throw new \Exception('Failed to create game or game ID is missing');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Game created successfully',
                'game' => $game
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error creating game: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create game',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
