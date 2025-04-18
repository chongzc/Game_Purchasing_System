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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Get the authenticated user's profile
     */
    public function profile()
    {
        $user = Auth::user();
        
        if (!$user) {
            $user = User::first();
            if (!$user) {
                return response()->json(['message' => 'No users in database'], 404);
            }
        }

        return response()->json([
            'u_id' => $user->u_id,
            'u_name' => $user->u_name,
            'u_email' => $user->u_email,
            'u_birthdate' => $user->u_birthdate,
            'u_role' => $user->u_role,
            'u_profileImagePath' => $user->u_profileImagePath,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ]);
    }

    /**
     * Update the user's profile
     */
    public function updateProfile(Request $request)
    {
        try {
            // Log request details
            Log::info('Profile update request details', [
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
            $validator = Validator::make($request->all(), [
                'name' => ['sometimes', 'string', 'max:255', Rule::unique('users', 'u_name')->ignore($user->u_id, 'u_id')],
                'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'u_email')->ignore($user->u_id, 'u_id')],
                'birthdate' => ['sometimes', 'date', 'before:today'],
                'current_password' => ['sometimes', 'required_with:new_password'],
                'new_password' => ['sometimes', 'required_with:current_password', 'min:8'],
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $validated = $validator->validated();
    
            // Handle profile picture upload if present
            if ($request->hasFile('profile_picture')) {
                try {
                    $file = $request->file('profile_picture');
                    
                    // Log file details
                    Log::info('File details', [
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'error' => $file->getError()
                    ]);
                    
                    // Create directory if it doesn't exist
                    $uploadPath = public_path('images/user_profile');
                    if (!File::exists($uploadPath)) {
                        File::makeDirectory($uploadPath, 0777, true);
                        Log::info('Created directory: ' . $uploadPath);
                    }
                    
                    // Delete previous profile picture if it exists
                    if ($user->u_profileImagePath) {
                        $previousImagePath = public_path($user->u_profileImagePath);
                        if (File::exists($previousImagePath)) {
                            File::delete($previousImagePath);
                            Log::info('Deleted previous profile picture: ' . $previousImagePath);
                        }
                    }
                    
                    // Generate unique filename
                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    
                    // Move file to public directory
                    $file->move($uploadPath, $filename);
                    Log::info('File moved to: ' . $uploadPath . '/' . $filename);
                    
                    // Store file path in database
                    $user->u_profileImagePath = 'images/user_profile/' . $filename;
                } catch (\Exception $e) {
                    Log::error('File upload error: ' . $e->getMessage());
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
            
            // Handle password update if both current and new passwords are provided
            if ($request->has('current_password') && $request->has('new_password')) {
                // Verify current password
                if (!Hash::check($request->current_password, $user->u_password)) {
                    return response()->json([
                        'message' => 'Validation failed',
                        'errors' => ['current_password' => ['Current password is incorrect']]
                    ], 422);
                }
                
                // Update password
                $user->u_password = Hash::make($request->new_password);
            }
    
            $user->save();
            
            // Log successful update
            Log::info('Profile updated for user ID: ' . $user->u_id);
    
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
            Log::error('Profile update error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
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
        try {
            // Get games in user's wishlist with their details
            $wishlist = Game::whereIn('g_id', function($query) {
                $query->select('wl_gameId')
                    ->from('wishlist')
                    ->where('wl_userId', Auth::id());
            })
            ->with('developer')
            ->get()
            ->map(function ($game) {
                return [
                    'id' => $game->g_id,
                    'title' => $game->g_title,
                    'price' => $game->g_price,
                    'discount' => $game->g_discount,
                    'image_url' => $game->g_mainImage,
                    'developer' => $game->developer->u_name
                ];
            });

            return response()->json($wishlist);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add game to wishlist
     */
    public function addToWishlist(Game $game)
    {
        try {
            $user = Auth::user();
            
            // Check if game already exists in wishlist
            $exists = Wishlist::where('wl_userId', $user->u_id)
                ->where('wl_gameId', $game->g_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Game already in wishlist'
                ], 409);
            }

            // Add game to wishlist
            Wishlist::create([
                'wl_userId' => $user->u_id,
                'wl_gameId' => $game->g_id,
                'wl_name' => $game->g_title
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Game added to wishlist successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add game to wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove game from wishlist
     */
    public function removeFromWishlist(Game $game)
    {
        try {
            $user = Auth::user();
            
            // Remove game from wishlist
            $deleted = Wishlist::where('wl_userId', $user->u_id)
                ->where('wl_gameId', $game->g_id)
                ->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Game not found in wishlist'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Game removed from wishlist successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove game from wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
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

    /**
     * Get all users from the database
     */
    public function getUsers()
    {
        $users = User::select('u_id', 'u_name', 'u_email', 'u_role', 'u_profileImagePath', 'created_at')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->u_id,
                    'name' => $user->u_name,
                    'email' => $user->u_email,
                    'role' => $user->u_role,
                    'profilePic' => $user->u_profileImagePath ? asset($user->u_profileImagePath) : null,
                    'created_at' => $user->created_at
                ];
            });

        return response()->json($users);
    }
}
