<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameCategory;
use App\Models\UserLib;
use App\Models\User;
use App\Models\Review;
use App\Models\Wishlist;
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
            ->orderBy('g_overallRate', 'desc')
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $game = Game::with(['developer', 'categories', 'reviews.user'])
                ->where('g_id', $id)
                ->firstOrFail();

            // Get user's library status if authenticated
            $libraryStatus = null;
            $isWishlisted = false;
            if (Auth::check()) {
                $libraryGame = UserLib::where('ul_userId', Auth::user()->u_id)
                    ->where('ul_gameId', $id)
                    ->first();
                $libraryStatus = $libraryGame ? $libraryGame->ul_status : null;
                
                // Check if game is in user's wishlist
                $isWishlisted = Wishlist::where('wl_userId', Auth::id())
                    ->where('wl_gameId', $id)
                    ->exists();
            }

            // Calculate rating breakdown
            $ratingBreakdown = [
                5 => 0,
                4 => 0,
                3 => 0,
                2 => 0,
                1 => 0
            ];

            foreach ($game->reviews as $review) {
                if (isset($ratingBreakdown[$review->r_rating])) {
                    $ratingBreakdown[$review->r_rating]++;
                }
            }

            // Calculate percentages for rating breakdown
            $totalReviews = $game->reviews->count();
            if ($totalReviews > 0) {
                foreach ($ratingBreakdown as $rating => $count) {
                    $ratingBreakdown[$rating] = round(($count / $totalReviews) * 100);
                }
            }

            // Get similar games based on categories
            $similarGames = Game::whereHas('categories', function($query) use ($game) {
                $query->whereIn('gc_category', $game->categories->pluck('gc_category'));
            })
            ->where('g_id', '!=', $id)
            ->where('g_status', 'verified')
            ->take(4)
            ->get()
            ->map(function ($game) {
                return [
                    'id' => $game->g_id,
                    'title' => $game->g_title,
                    'price' => $game->g_price,
                    'discount' => $game->g_discount,
                    'rating' => $game->g_overallRate,
                    'image' => $game->g_mainImage ? asset('storage/' . $game->g_mainImage) : null
                ];
            });

            // Transform the game data
            $gameData = [
                'id' => $game->g_id,
                'title' => $game->g_title,
                'description' => $game->g_description,
                'fullDescription' => $game->g_description,
                'price' => $game->g_price,
                'originalPrice' => $game->g_price,
                'discount' => $game->g_discount,
                'rating' => $game->g_overallRate,
                'reviewCount' => $totalReviews,
                'category' => $game->g_category,
                'developer' => $game->developer->u_name,
                'releaseDate' => $game->created_at->format('M d, Y'),
                'platform' => 'PC',
                'mainImage' => $game->g_mainImage ? asset('storage/' . $game->g_mainImage) : null,
                'gallery' => array_filter([
                    $game->g_mainImage ? asset('storage/' . $game->g_mainImage) : null,
                    $game->g_exImg1 ? asset('storage/' . $game->g_exImg1) : null,
                    $game->g_exImg2 ? asset('storage/' . $game->g_exImg2) : null,
                    $game->g_exImg3 ? asset('storage/' . $game->g_exImg3) : null
                ]),
                'features' => $game->categories->pluck('gc_category')->toArray(),
                'ratingBreakdown' => $ratingBreakdown,
                'reviews' => $game->reviews->map(function ($review) {
                    return [
                        'userName' => $review->user->u_name,
                        'userProfileImage' => $review->user->u_profileImagePath ? asset($review->user->u_profileImagePath) : null,
                        'rating' => $review->r_rating,
                        'date' => $review->created_at->format('M d, Y'),
                        'comment' => $review->r_reviewText
                    ];
                }),
                'libraryStatus' => $libraryStatus,
                'isWishlisted' => $isWishlisted
            ];

            return response()->json([
                'success' => true,
                'game' => $gameData,
                'similarGames' => $similarGames
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch game details',
                'error' => $e->getMessage()
            ], 500);
        }
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
                    'image' => $lib->game->g_mainImage ? asset('storage/' . $lib->game->g_mainImage) : null,
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
                        'mainImage' => $game->g_mainImage ? asset('storage/' . $game->g_mainImage) : null,
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
            $userLibraryGameIds = UserLibraryController::getUserLibraryGameIds();

            // Apply tab filters
            switch ($request->tab) {
                case 'trending':
                    $query->where('created_at', '>', now()->subDays(90))->orderBy("created_at", "desc");
                    break;
                case 'most-review':
                    $query->withCount('reviews')
                        ->orderBy('reviews_count', 'desc');
                    break;
                case 'top-rated':
                    $query->orderBy('g_overallRate', 'desc');
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

            // Get the games
            $games = $query->get()->map(function ($game) use ($userLibraryGameIds) {
                return [
                    'id' => $game->g_id,
                    'title' => $game->g_title,
                    'image' => $game->g_mainImage ? asset('storage/' . $game->g_mainImage) : null,
                    'price' => $game->g_price,
                    'originalPrice' => $game->g_price,
                    'discount' => $game->g_discount,
                    'onSale' => $game->g_discount > 0,
                    'reviewStatus' => ReviewController::getReviewStatus($game->g_overallRate),
                    'reviewCount' => Review::where('r_gameId', $game->g_id)->count(),
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
      * Get game details for editing
      *
      * @param int $id
      * @return \Illuminate\Http\JsonResponse
      */
      public function getGameForEdit($id)
      {
          try {
              $game = Game::with('categories')
                  ->where('g_id', $id)
                  ->where('g_developerId', Auth::id())
                  ->firstOrFail();
  
              $categories = $game->categories->pluck('gc_category')->toArray();
              $categoryIds = [];
              
              // Map category names to IDs
              foreach ($categories as $category) {
                  switch ($category) {
                      case 'Action': $categoryIds[] = 1; break;
                      case 'Adventure': $categoryIds[] = 2; break;
                      case 'RPG': $categoryIds[] = 3; break;
                      case 'Strategy': $categoryIds[] = 4; break;
                      case 'Sports': $categoryIds[] = 5; break;
                      case 'Racing': $categoryIds[] = 6; break;
                      case 'Simulation': $categoryIds[] = 7; break;
                      case 'Puzzle': $categoryIds[] = 8; break;
                      case 'Platformer': $categoryIds[] = 9; break;
                      case 'Fighting': $categoryIds[] = 10; break;
                      case 'Shooter': $categoryIds[] = 11; break;
                      default: $categoryIds[] = 1; break;
                  }
              }
  
              $gameData = [
                  'id' => $game->g_id,
                  'title' => $game->g_title,
                  'description' => $game->g_description,
                  'price' => $game->g_price,
                  'discount' => $game->g_discount,
                  'genre' => $game->g_category,
                  'language' => $game->g_language,
                  'releaseDate' => $game->created_at->format('Y-m-d'),
                  'status' => $game->g_status,
                  'mainImage' => $game->g_mainImage ? asset('storage/' . $game->g_mainImage) : null,
                  'screenshots' => array_filter([
                      $game->g_exImg1 ? asset('storage/' . $game->g_exImg1) : null,
                      $game->g_exImg2 ? asset('storage/' . $game->g_exImg2) : null,
                      $game->g_exImg3 ? asset('storage/' . $game->g_exImg3) : null
                  ]),
                  'categoryIds' => $categoryIds
              ];
  
              return response()->json([
                  'success' => true,
                  'game' => $gameData
              ]);
          } catch (\Exception $e) {
              return response()->json([
                  'success' => false,
                  'message' => 'Failed to get game for editing',
                  'error' => $e->getMessage()
              ], 500);
          }
      }
      
      /**
       * Update the specified game.
       *
       * @param  \Illuminate\Http\Request  $request
       * @param  int  $id
       * @return \Illuminate\Http\JsonResponse
       */
      public function update(Request $request, $id)
      {
          try {
              Log::info('Game update request:', $request->all());
              
              // Find the game and check ownership
              $game = Game::where('g_id', $id)
                  ->where('g_developerId', Auth::id())
                  ->firstOrFail();
              
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
                  'g_mainImage' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                  'g_exImg1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                  'g_exImg2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                  'g_exImg3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
              ]);
              
              // Handle file uploads - only update if new files are provided
              if ($request->hasFile('g_mainImage')) {
                  // Delete the old image if it exists
                  if ($game->g_mainImage) {
                      Storage::delete('public/' . $game->g_mainImage);
                  }
                  $game->g_mainImage = $request->file('g_mainImage')->store('games', 'public');
              }
              
              if ($request->hasFile('g_exImg1')) {
                  if ($game->g_exImg1) {
                      Storage::delete('public/' . $game->g_exImg1);
                  }
                  $game->g_exImg1 = $request->file('g_exImg1')->store('games', 'public');
              }
              
              if ($request->hasFile('g_exImg2')) {
                  if ($game->g_exImg2) {
                      Storage::delete('public/' . $game->g_exImg2);
                  }
                  $game->g_exImg2 = $request->file('g_exImg2')->store('games', 'public');
              }
              
              if ($request->hasFile('g_exImg3')) {
                  if ($game->g_exImg3) {
                      Storage::delete('public/' . $game->g_exImg3);
                  }
                  $game->g_exImg3 = $request->file('g_exImg3')->store('games', 'public');
              }
              
              // Update game basic info
              $game->g_title = $request->g_title;
              $game->g_description = $request->g_description;
              $game->g_price = $request->g_price;
              $game->g_discount = $request->g_discount ?? 0;
              $game->g_status = $request->g_status ?? 'pending'; // Use provided status or default to pending
              $game->g_language = $request->g_language;
              $game->g_category = $request->g_category;
              
              // Save the game
              $game->save();
              
              // Update categories
              if (isset($request->categories) && is_array($request->categories)) {
                  // Delete existing categories
                  GameCategory::where('gc_gameId', $game->g_id)->delete();
                  
                  // Add new categories
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
                          default: $categoryName = 'Action'; break;
                      }
                      
                      GameCategory::create([
                          'gc_gameId' => $game->g_id,
                          'gc_gameName' => $game->g_title,
                          'gc_category' => $categoryName
                      ]);
                      
                      // Update the game's main category if not already set
                      if (!$game->g_category) {
                          $game->g_category = $categoryName;
                          $game->save();
                      }
                  }
              }
              
              return response()->json([
                  'success' => true,
                  'message' => 'Game updated successfully',
                  'game' => $game
              ]);
              
          } catch (\Exception $e) {
              Log::error('Error updating game: ' . $e->getMessage());
              return response()->json([
                  'success' => false,
                  'message' => 'Failed to update game',
                  'error' => $e->getMessage()
              ], 500);
          }
      }
}
