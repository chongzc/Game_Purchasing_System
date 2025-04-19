<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /****************************
     * GAME MANAGEMENT METHODS *
     ****************************/
    
    /**
     * Display a listing of all games for admin
     */
    public function getAllGames()
    {
        $games = Game::with(['developer'])
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
    public function getGame($id)
    {
        $game = Game::with(['developer', 'reviews'])
            ->findOrFail($id);
            
        return response()->json([
            'success' => true,
            'game' => $game
        ]);
    }
    
    /**
     * Update game status
     */
    public function updateGameStatus(Request $request, $id)
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
    public function deleteGame($id)
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
        
        // Delete the game
        $game->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Game deleted successfully'
        ]);
    }
    
    /****************************
     * USER MANAGEMENT METHODS *
     ****************************/
    
    /**
     * Get all users for admin
     */
    public function getAllUsers()
    {
        $users = User::select('u_id', 'u_name', 'u_email', 'u_role', 'u_profileImagePath', 'u_isBanned', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->u_id,
                    'name' => $user->u_name,
                    'email' => $user->u_email,
                    'role' => $user->u_role,
                    'isBanned' => $user->u_isBanned,
                    'profilePic' => $user->u_profileImagePath,
                    'createdAt' => $user->created_at,
                ];
            });
            
        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }
    
    /**
     * Get a specific user for admin
     */
    public function getUser($id)
    {
        $user = User::findOrFail($id);
        
        $userData = [
            'id' => $user->u_id,
            'name' => $user->u_name,
            'email' => $user->u_email,
            'role' => $user->u_role,
            'isBanned' => $user->u_isBanned,
            'profilePic' => $user->u_profileImagePath,
            'createdAt' => $user->created_at,
        ];
        
        return response()->json([
            'success' => true,
            'user' => $userData
        ]);
    }
    
    /**
     * Update user ban status
     */
    public function updateUserBanStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'isBanned' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($id);
        $user->u_isBanned = $request->isBanned ? 'true' : 'false';
        $user->save();
        
        $status = $request->isBanned ? 'banned' : 'unbanned';
        
        return response()->json([
            'success' => true,
            'message' => "User {$user->u_name} has been {$status} successfully",
            'user' => [
                'id' => $user->u_id,
                'name' => $user->u_name,
                'email' => $user->u_email,
                'role' => $user->u_role,
                'isBanned' => $user->u_isBanned,
            ]
        ]);
    }
    
    /**
     * Find a user by name for admin
     */
    public function findUserByName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $name = $request->name;
        
        $users = User::where('u_name', 'like', "%{$name}%")
            ->select('u_id', 'u_name', 'u_email', 'u_role', 'u_profileImagePath', 'u_isBanned')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->u_id,
                    'name' => $user->u_name,
                    'email' => $user->u_email,
                    'role' => $user->u_role,
                    'isBanned' => $user->u_isBanned,
                    'profilePic' => $user->u_profileImagePath,
                ];
            });
            
        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }
    
    /****************************
     * DASHBOARD STATISTICS METHODS *
     ****************************/
    
    /**
     * Get games statistics for admin dashboard
     */
    public function getGameStatistics()
    {
        $totalGames = Game::count();
        $pendingGames = Game::where('g_status', 'pending')->count();
        $verifiedGames = Game::where('g_status', 'verified')->count();
        $reportedGames = Game::where('g_status', 'reported')->count();
        
        $topCategories = Game::select('g_category as name', DB::raw('count(*) as games_count'))
            ->groupBy('g_category')
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
     * Get all statistics for admin dashboard.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllStatistics()
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
                'bannedUsers' => User::where('u_isBanned', 'true')->count(),
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
