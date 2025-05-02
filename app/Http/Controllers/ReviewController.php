<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    /**
     * Get review status based on rating
     *
     * @param float $rating
     * @return string
     */
    public static function getReviewStatus($rating)
    {
        if ($rating >= 4.5) return 'Overwhelmingly Positive';
        if ($rating >= 4.0) return 'Very Positive';
        if ($rating >= 3.5) return 'Positive';
        if ($rating >= 3.0) return 'Mostly Positive';
        if ($rating >= 2.5) return 'Mixed';
        if ($rating >= 2.0) return 'Mostly Negative';
        return 'Negative';
    }

    /**
     * Get reviews for a specific game
     *
     * @param int $gameId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReviews($gameId)
    {
        try {
            $reviews = Review::where('r_gameId', $gameId)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($review) {
                    return [
                        'id' => $review->r_id,
                        'rating' => $review->r_rating,
                        'comment' => $review->r_reviewText,
                        'created_at' => $review->created_at,
                        'user' => [
                            'id' => $review->user->u_id,
                            'name' => $review->user->u_name,
                            'avatar' => $this->getProfilePicUrl($review->user->u_profileImagePath)
                        ]
                    ];
                });

            return response()->json([
                'success' => true,
                'reviews' => $reviews
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reviews',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to get the complete profile picture URL
     */
    private function getProfilePicUrl($path)
    {
        if (!$path) {
            Log::info('getProfilePicUrl: Path is null or empty');
            return null;
        }
        
        Log::info('getProfilePicUrl: Processing path', ['path' => $path]);
        
        // If it's already a complete URL, return it
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            Log::info('getProfilePicUrl: Path is already a URL');
            return $path;
        }
        
        // If it looks like a local storage path (starts with user_profile/), construct S3 URL
        if (Str::startsWith($path, 'user_profile/')) {
            $bucket = env('AWS_BUCKET');
            $region = env('AWS_DEFAULT_REGION');
            $url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
            Log::info('getProfilePicUrl: Constructed S3 URL', ['url' => $url]);
            return $url;
        }
        
        // For local storage paths
        $url = asset('storage/' . $path);
        Log::info('getProfilePicUrl: Local storage URL', ['url' => $url]);
        return $url;
    }

    /**
     * Get user's review for a specific game
     *
     * @param int $gameId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserReview($gameId)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $review = Review::where('r_gameId', $gameId)
                ->where('r_userId', Auth::user()->u_id)
                ->first();

            if (!$review) {
                return response()->json([
                    'success' => true,
                    'review' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'review' => [
                    'id' => $review->r_id,
                    'rating' => $review->r_rating,
                    'comment' => $review->r_reviewText,
                    'created_at' => $review->created_at
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit a review for a game
     *
     * @param Request $request
     * @param int $gameId
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitReview(Request $request, $gameId)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validated = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|max:1000'
            ]);

            // Get the game
            $game = Game::findOrFail($gameId);
            
            // Check if user is authorized to submit a review
            if (!Auth::user()->can('review', $game)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to submit reviews. Only regular users can submit reviews.'
                ], 403);
            }

            $review = Review::updateOrCreate(
                [
                    'r_userId' => Auth::id(),
                    'r_gameId' => $gameId
                ],
                [
                    'r_rating' => $validated['rating'],
                    'r_reviewText' => $validated['comment']
                ]
            );

            // Calculate new average rating
            $averageRating = Review::where('r_gameId', $gameId)
                ->avg('r_rating');

            // Update game's overall rating
            $game->g_overallRate = round($averageRating, 2);
            $game->save();

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully',
                'review' => [
                    'id' => $review->r_id,
                    'rating' => $review->r_rating,
                    'comment' => $review->r_reviewText,
                    'created_at' => $review->created_at
                ],
                'newOverallRating' => $game->g_overallRate
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 
