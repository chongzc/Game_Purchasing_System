<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminGameController extends Controller
{
    /**
     * Display a listing of all games for admin
     */
    public function index()
    {
        $games = Game::with(['developer', 'categories'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'games' => $games
        ]);
    }
    
    /**
     * Get a specific game for admin
     */
    public function show($id)
    {
        $game = Game::with(['developer', 'categories', 'reviews'])
            ->findOrFail($id);
            
        return response()->json([
            'success' => true,
            'game' => $game
        ]);
    }
    
    /**
     * Update game status
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,verified,reported,removed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $game = Game::findOrFail($id);
        $game->g_status = $request->status;
        $game->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Game status updated successfully',
            'game' => $game
        ]);
    }
    
    /**
     * Remove a game
     */
    public function destroy($id)
    {
        $game = Game::findOrFail($id);
        
        // Delete game image if exists
        if ($game->g_mainImage && !str_contains($game->g_mainImage, 'placeholder')) {
            Storage::disk('public')->delete($game->g_mainImage);
        }
        
        // Delete associated images
        if ($game->g_exImg1) {
            Storage::delete('public/' . $game->g_exImg1);
        }
        if ($game->g_exImg2) {
            Storage::delete('public/' . $game->g_exImg2);
        }
        if ($game->g_exImg3) {
            Storage::delete('public/' . $game->g_exImg3);
        }
        
        // Delete associated categories
        GameCategory::where('gc_gameId', $id)->delete();
        
        // Delete the game
        $game->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Game deleted successfully'
        ]);
    }
    
    /**
     * Get games statistics for admin dashboard
     */
    public function statistics()
    {
        $totalGames = Game::count();
        $pendingGames = Game::where('g_status', 'pending')->count();
        $verifiedGames = Game::where('g_status', 'verified')->count();
        $reportedGames = Game::where('g_status', 'reported')->count();
        
        $topCategories = DB::table('game_categories')
            ->select('gc_category as name', DB::raw('count(*) as games_count'))
            ->groupBy('gc_category')
            ->orderBy('games_count', 'desc')
            ->take(5)
            ->get();
            
        $recentGames = Game::with('developer')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        return response()->json([
            'success' => true,
            'statistics' => [
                'total_games' => $totalGames,
                'pending_games' => $pendingGames,
                'verified_games' => $verifiedGames,
                'reported_games' => $reportedGames,
                'top_categories' => $topCategories,
                'recent_games' => $recentGames
            ]
        ]);
    }

    /**
     * Get statistics for admin dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics()
    {
        try {
            $statistics = [
                'totalGames' => Game::count(),
                'pendingGames' => Game::where('g_status', 'pending')->count(),
                'verifiedGames' => Game::where('g_status', 'verified')->count(),
                'reportedGames' => Game::where('g_status', 'reported')->count(),
                'removedGames' => Game::where('g_status', 'removed')->count(),
                'totalDevelopers' => User::where('u_role', 'developer')->count(),
                'totalUsers' => User::where('u_role', 'user')->count(),
                'totalAdmins' => User::where('u_role', 'admin')->count(),
            ];

            return response()->json([
                'success' => true,
                'statistics' => $statistics
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching admin statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 
