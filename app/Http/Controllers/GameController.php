<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameCategory;
use App\Models\UserLib;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use League\CommonMark\Extension\CommonMark\Parser\Inline\EscapableParser;

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
            ->where('ul_userId', Auth::user()->user_id)
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
                'g_developerId' => $request->user()->u_id,
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
                        
                        // For the first category, update the game's main category if it's not already set
                        if (!$game->g_category) {
                            $game->g_category = $categoryName;
                            $game->save();
                        }
                    }
                }
            } else {
                throw new \Exception('Failed to create game or game ID is missing');
            }
            
            // Refresh the game to get the updated data including any changes made during category setup
            $game = Game::find($game->g_id);
            
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

    /**
     * Get all games for the authenticated developer
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDeveloperGames()
    {
        try {
            $games = Game::where('g_developerId', Auth::user()->u_id)
                ->with(['categories' => function($query) {
                    $query->select('gc_gameId', 'gc_category');
                }])
                ->get()
                ->map(function ($game) {
                    return [
                        'id' => $game->g_id,
                        'title' => $game->g_title,
                        'price' => $game->g_price,
                        'discount' => $game->g_discount,
                        'status' => $game->g_status,
                        'mainImage' => $game->g_mainImage,
                        'downloadCount' => $game->g_downloadCount,
                        'overallRate' => $game->g_overallRate,
                        'categories' => $game->categories->pluck('gc_category'),
                        'created_at' => $game->created_at
                    ];
                });

            return response()->json([
                'success' => true,
                'games' => $games
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching developer games: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch games',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get distinct game categories with counts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories()
    {
        try {
            $categories = GameCategory::select('gc_category')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('gc_category')
                ->orderBy('count', 'desc')
                ->get()
                ->map(function ($category) {
                    return [
                        'name' => $category->gc_category,
                        'count' => $category->count
                    ];
                });

            return response()->json([
                'success' => true,
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get distinct game languages with counts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLanguages()
    {
        try {
            $languages = Game::select('g_language')
                ->selectRaw('COUNT(*) as count')
                ->where('g_status', 'verified')
                ->groupBy('g_language')
                ->orderBy('count', 'desc')
                ->get()
                ->map(function ($lang) {
                    return [
                        'name' => $lang->g_language,
                        'count' => $lang->count
                    ];
                });

            return response()->json([
                'success' => true,
                'languages' => $languages
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching languages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch languages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's library game IDs
     *
     * @return array
     */
    private function getUserLibraryGameIds()
    {
        if (!Auth::check()) {
            return [];
        }

        return UserLib::where('ul_userId', Auth::user()->u_id)
            ->pluck('ul_gameId')
            ->toArray();
    }

    /**
     * Browse games with filters
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function browseGames(Request $request)
    {
        try {
            $query = Game::with(['categories', 'developer'])
                ->where('g_status', 'verified');

            // Get user's library game IDs
            $userLibraryGameIds = $this->getUserLibraryGameIds();

            // Apply tab filters
            switch ($request->tab) {
                case 'trending':
                    $query->where('created_at', '>', now()->subDays(90))->orderBy("created_at", "desc");
                    break;
                case 'top-sellers':
                    $query->orderBy('g_downloadCount', 'desc');
                    break;
                case 'top-rated':
                    $query->where('g_overallRate', '>=', 4);
                    break;
            }

            // Apply price filter
            if ($request->has('maxPrice')) {
                $query->where('g_price', '<=', $request->maxPrice);
            }

            // Apply category filter
            if ($request->has('category')) {
                $query->whereHas('categories', function($q) use ($request) {
                    $q->where('gc_category', $request->category);
                });
            }

            // Apply language filter
            if ($request->has('language')) {
                $query->where('g_language', $request->language);
            }

            // Apply search filter
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('g_title', 'like', "%{$search}%")
                      ->orWhereHas('categories', function($q) use ($search) {
                          $q->where('gc_category', 'like', "%{$search}%");
                      });
                });
            }

            $games = $query->get()->map(function ($game) use ($userLibraryGameIds) {
                return [
                    'id' => $game->g_id,
                    'title' => $game->g_title,
                    'image' => $game->g_mainImage,
                    'price' => $game->g_price,
                    'originalPrice' => $game->g_price,
                    'discount' => $game->g_discount,
                    'onSale' => $game->g_discount > 0,
                    'reviewStatus' => $this->getReviewStatus($game->g_overallRate),
                    'reviewCount' => $game->g_downloadCount,
                    'releaseDate' => $game->created_at,
                    'multiPlayer' => true,
                    'openWorld' => false,
                    'tags' => $game->categories->pluck('gc_category')->toArray(),
                    'inLibrary' => in_array($game->g_id, $userLibraryGameIds)
                ];
            });

            return response()->json([
                'success' => true,
                'games' => $games
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching games: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch games',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get review status based on rating
     *
     * @param float $rating
     * @return string
     */
    private function getReviewStatus($rating)
    {
        if ($rating >= 4.5) return 'Overwhelmingly Positive';
        if ($rating >= 4.0) return 'Very Positive';
        if ($rating >= 3.5) return 'Positive';
        if ($rating >= 3.0) return 'Mostly Positive';
        if ($rating >= 2.5) return 'Mixed';
        if ($rating >= 2.0) return 'Mostly Negative';
        return 'Negative';
    }

    public function getLibraryGames(Request $request)
    {
        $userLibraryIds = $this->getUserLibraryGameIds();
        
        // Get user library details with status filter
        $libraryQuery = UserLib::where('ul_userId', Auth::user()->u_id)
            ->whereIn('ul_gameId', $userLibraryIds);

        // Apply status filter
        if ($request->has('status')) {
            if ($request->status === 'all') {
                $libraryQuery->whereIn('ul_status', ['owned', 'installed']);
            } elseif ($request->status !== '') {
                $libraryQuery->where('ul_status', $request->status);
            }
        }

        $libraryDetails = $libraryQuery->get()->keyBy('ul_gameId');
        $filteredGameIds = $libraryDetails->pluck('ul_gameId')->toArray();

        // Get games that match the filtered library entries
        $games = Game::select('games.*')
            ->with(['developer'])
            ->whereIn('g_id', $filteredGameIds)
            ->get();

        // Combine game data with library details
        $libraryGames = $games->map(function ($game) use ($libraryDetails) {
            $libraryInfo = $libraryDetails[$game->g_id];
            return [
                'game' => [
                    'g_id' => $game->g_id,
                    'g_title' => $game->g_title,
                    'g_image' => $game->g_mainImage,
                    'g_price' => $game->g_price,
                    'g_description' => $game->g_description,
                    'developer' => $game->developer
                ],
                'ul_createdAt' => $libraryInfo->created_at,
                'ul_status' => $libraryInfo->ul_status
            ];
        });

        return response()->json($libraryGames);
    }
}
