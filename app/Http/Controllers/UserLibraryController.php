<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\UserLib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserLibraryController extends Controller
{
    /**
     * Get user's library game IDs
     *
     * @return array
     */
    public static function getUserLibraryGameIds()
    {
        if (!Auth::check()) {
            return [];
        }

        return UserLib::where('ul_userId', Auth::user()->u_id)
            ->pluck('ul_gameId')
            ->toArray();
    }

    /**
     * Get games in user's library with filters
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
                    'g_image' => $game->g_mainImage ? asset('storage/' . $game->g_mainImage) : null,
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

    /**
     * Update game status in library
     *
     * @param Request $request
     * @param Game $game
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLibraryStatus(Request $request, Game $game)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['owned', 'installed'])]
        ]);

        $user = Auth::user();
        
        $userLib = UserLib::where('ul_userId', $user->u_id)
            ->where('ul_gameId', $game->g_id)
            ->first();

        if (!$userLib) {
            return response()->json([
                'success' => false,
                'message' => 'Game not found in library'
            ], 404);
        }

        // Allow changing between owned and installed in both directions
        if (
            ($userLib->ul_status === 'owned' && $validated['status'] === 'installed') ||
            ($userLib->ul_status === 'installed' && $validated['status'] === 'owned')
        ) {
            $userLib->ul_status = $validated['status'];
            $userLib->save();

            return response()->json([
                'success' => true,
                'message' => $validated['status'] === 'installed' ? 'Game installed successfully' : 'Game uninstalled successfully',
                'status' => $validated['status']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid status change',
            'current_status' => $userLib->ul_status
        ], 400);
    }

    /**
     * Add multiple games to user's library
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToLibrary(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false, 
                'message' => 'User not authenticated'
            ], 401);
        }
        
        $validated = $request->validate([
            'games' => 'required|array',
            'games.*.ul_gameId' => 'required|exists:games,g_id',
            'games.*.ul_name' => 'required|string',
            'games.*.ul_status' => ['required', Rule::in(['owned', 'installed'])],
        ]);
        
        $userId = Auth::user()->u_id;
        $games = $validated['games'];
        $addedCount = 0;
        
        foreach ($games as $game) {
            // Check if game already exists in library
            $existingEntry = UserLib::where('ul_userId', $userId)
                ->where('ul_gameId', $game['ul_gameId'])
                ->first();
                
            if (!$existingEntry) {
                // Add game to library
                UserLib::create([
                    'ul_name' => $game['ul_name'],
                    'ul_gameId' => $game['ul_gameId'],
                    'ul_userId' => $userId,
                    'ul_status' => $game['ul_status'],
                ]);
                
                $addedCount++;
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => $addedCount . ' game(s) added to library',
            'count' => $addedCount
        ]);
    }
} 
