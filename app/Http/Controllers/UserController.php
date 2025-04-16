<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Game;
use App\Models\UserLib;
use App\Models\Wishlist;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class UserController extends Controller
{
    /**
     * Get the authenticated user's profile
     */
    public function profile()
    {
        // Try to get authenticated user
        $user = Auth::user();
        
        // For demo/assignment - if user not authenticated, get the first user
        if (!$user) {
            $user = User::first();
            if (!$user) {
                return response()->json(['message' => 'No users in database'], 404);
            }
        }

        return response()->json([
            'name' => $user->u_name,
            'email' => $user->u_email,
            'birthdate' => $user->u_birthdate,
            'role' => $user->u_role,
            'profilePic' => $user->u_profileImagePath ? asset($user->u_profileImagePath) : null
        ]);
    }

    /**
     * Update the user's profile
     */
    public function updateProfile(Request $request)
    {
        try {
            // Log request details
            \Log::info('Profile update request details', [
                'has_file' => $request->hasFile('profile_picture'),
                'all_files' => $request->allFiles(),
                'all_inputs' => $request->all()
            ]);
            
            // Find the current user
            $user = Auth::user();
            
            // For demo/assignment - if user not authenticated, get the first user
            if (!$user) {
                $user = User::first();
                if (!$user) {
                    return response()->json(['message' => 'No users in database'], 404);
                }
            }
            
            // Basic validation for the form fields
            $validated = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'birthdate' => $request->input('birthdate'),
            ];
    
            // Handle profile picture upload if present
            if ($request->hasFile('profile_picture')) {
                try {
                    $file = $request->file('profile_picture');
                    
                    // Log file details
                    \Log::info('File details', [
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'error' => $file->getError()
                    ]);
                    
                    // Create directory if it doesn't exist
                    $uploadPath = public_path('images/user_profile');
                    if (!File::exists($uploadPath)) {
                        File::makeDirectory($uploadPath, 0777, true);
                        \Log::info('Created directory: ' . $uploadPath);
                    }
                    
                    // Generate unique filename
                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    
                    // Move file to public directory
                    $file->move($uploadPath, $filename);
                    \Log::info('File moved to: ' . $uploadPath . '/' . $filename);
                    
                    // Store file path in database
                    $user->u_profileImagePath = 'images/user_profile/' . $filename;
                } catch (\Exception $e) {
                    \Log::error('File upload error: ' . $e->getMessage());
                    return response()->json([
                        'message' => 'Failed to upload profile picture: ' . $e->getMessage()
                    ], 500);
                }
            }
    
            // Update other user properties if they were provided
            if (isset($validated['name'])) {
                $user->u_name = $validated['name'];
            }
            
            if (isset($validated['email'])) {
                $user->u_email = $validated['email'];
            }
            
            if (isset($validated['birthdate'])) {
                $user->u_birthdate = $validated['birthdate'];
            }
    
            $user->save();
            
            // Log successful update
            \Log::info('Profile updated for user ID: ' . $user->u_id);
    
            // Return response with success message and user data
            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => [
                    'u_name' => $user->u_name,
                    'u_email' => $user->u_email,
                    'u_birthdate' => $user->u_birthdate,
                    'u_role' => $user->u_role,
                    'profilePic' => $user->u_profileImagePath ? asset($user->u_profileImagePath) : null
                ]
            ], 200, [
                'Content-Type' => 'application/json'
            ]);
        } catch (\Exception $e) {
            // Log the exception
            \Log::error('Profile update error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's game library
     */
    public function getLibrary()
    {
        $library = Auth::user()->gameLibrary()
            ->with('developer')
            ->get()
            ->map(function ($game) {
                return [
                    'id' => $game->g_id,
                    'title' => $game->g_title,
                    'status' => $game->pivot->ul_status,
                    'mainImage' => $game->g_mainImage,
                    'developer' => $game->developer->u_name
                ];
            });

        return response()->json($library);
    }

    /**
     * Update game status in library
     */
    public function updateLibraryStatus(Request $request, Game $game)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['owned', 'installed', 'removed'])]
        ]);

        $user = Auth::user();
        
        $userLib = UserLib::where('ul_userId', $user->u_id)
            ->where('ul_gameId', $game->g_id)
            ->first();

        if (!$userLib) {
            return response()->json(['message' => 'Game not found in library'], 404);
        }

        $userLib->ul_status = $validated['status'];
        $userLib->save();

        return response()->json([
            'message' => 'Game status updated successfully',
            'status' => $validated['status']
        ]);
    }

    /**
     * Get user's wishlist
     */
    public function getWishlist()
    {
        $wishlist = Auth::user()->wishlist()
            ->with('developer')
            ->get()
            ->map(function ($game) {
                return [
                    'id' => $game->g_id,
                    'title' => $game->g_title,
                    'price' => $game->g_price,
                    'discount' => $game->g_discount,
                    'mainImage' => $game->g_mainImage,
                    'developer' => $game->developer->u_name
                ];
            });

        return response()->json($wishlist);
    }

    /**
     * Add game to wishlist
     */
    public function addToWishlist(Game $game)
    {
        $user = Auth::user();
        
        if ($user->wishlist()->where('g_id', $game->g_id)->exists()) {
            return response()->json(['message' => 'Game already in wishlist'], 409);
        }

        $user->wishlist()->attach($game->g_id, ['wl_name' => $game->g_title]);

        return response()->json([
            'message' => 'Game added to wishlist successfully'
        ]);
    }

    /**
     * Remove game from wishlist
     */
    public function removeFromWishlist(Game $game)
    {
        $user = Auth::user();
        $user->wishlist()->detach($game->g_id);

        return response()->json([
            'message' => 'Game removed from wishlist successfully'
        ]);
    }

    /**
     * Get user's purchase history
     */
    public function getPurchaseHistory()
    {
        $purchases = Auth::user()->purchasedGames()
            ->with('developer')
            ->orderBy('purchases.created_at', 'desc')
            ->get()
            ->map(function ($game) {
                return [
                    'id' => $game->g_id,
                    'title' => $game->g_title,
                    'purchaseDate' => $game->pivot->p_purchaseDate,
                    'purchasePrice' => $game->pivot->p_purchasePrice,
                    'receiptNumber' => $game->pivot->p_receiptNumber,
                    'mainImage' => $game->g_mainImage,
                    'developer' => $game->developer->u_name
                ];
            });

        return response()->json($purchases);
    }
}
