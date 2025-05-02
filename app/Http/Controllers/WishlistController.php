<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WishlistController extends Controller
{
    /**
     * Helper method to get the proper URL for game images
     * 
     * @param string|null $path
     * @return string|null
     */
    private function getGameImageUrl($path)
    {
        if (!$path) {
            Log::info('getGameImageUrl: Path is null or empty');
            return null;
        }
        
        Log::info('getGameImageUrl: Processing path', ['path' => $path]);
        
        // If it's already a complete URL, return it
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            Log::info('getGameImageUrl: Path is already a URL');
            return $path;
        }
        
        // If it looks like an S3 key (starts with games/), construct S3 URL
        if (strpos($path, 'games/') === 0) {
            $bucket = env('AWS_BUCKET');
            $region = env('AWS_DEFAULT_REGION');
            $url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
            Log::info('getGameImageUrl: Constructed S3 URL', ['url' => $url]);
            return $url;
        }
        
        // For local storage paths
        $url = asset('storage/' . $path);
        Log::info('getGameImageUrl: Local storage URL', ['url' => $url]);
        return $url;
    }

    /**
     * Get user's wishlist status for a specific game
     *
     * @param int $gameId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWishlistStatus($gameId)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $isWishlisted = Wishlist::where('wl_userId', Auth::id())
                ->where('wl_gameId', $gameId)
                ->exists();

            return response()->json([
                'success' => true,
                'isWishlisted' => $isWishlisted
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking wishlist status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check wishlist status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's wishlist
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWishlist()
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $wishlist = Wishlist::where('wl_userId', Auth::id())
                ->with('game')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->game->g_id,
                        'title' => $item->game->g_title,
                        'price' => $item->game->g_price,
                        'discount' => $item->game->g_discount,
                        'image' => $this->getGameImageUrl($item->game->g_mainImage),
                        'rating' => $item->game->g_overallRate,
                        'addedAt' => $item->created_at
                    ];
                });

            return response()->json([
                'success' => true,
                'wishlist' => $wishlist
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching wishlist: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a game to user's wishlist
     *
     * @param int $gameId
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToWishlist($gameId)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Check if game exists
            $game = Game::findOrFail($gameId);

            // Check if already in wishlist
            $existing = Wishlist::where('wl_userId', Auth::id())
                ->where('wl_gameId', $gameId)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Game already in wishlist'
                ]);
            }

            // Add to wishlist
            Wishlist::create([
                'wl_userId' => Auth::id(),
                'wl_gameId' => $gameId,
                'wl_name' => $game->g_title
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Game added to wishlist'
            ]);
        } catch (\Exception $e) {
            Log::error('Error adding to wishlist: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add game to wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a game from user's wishlist
     *
     * @param int $gameId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFromWishlist($gameId)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $deleted = Wishlist::where('wl_userId', Auth::id())
                ->where('wl_gameId', $gameId)
                ->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Game not found in wishlist'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Game removed from wishlist'
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing from wishlist: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove game from wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 
