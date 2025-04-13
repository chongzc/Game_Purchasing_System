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

class UserController extends Controller
{
    /**
     * Get the authenticated user's profile
     */
    public function profile()
    {
        $user = Auth::user();
        // Eager load profile image but prevent JSON encoding of binary data
        $user->load(['profileImage' => function($query) {
            $query->select('img_id', 'img_filename', 'img_filetype', 'img_filesize');
        }]);

        return response()->json([
            'name' => $user->u_name,
            'email' => $user->u_email,
            'birthdate' => $user->u_birthdate,
            'role' => $user->u_role,
            'profilePic' => $user->profileImage?->getDataUrl()
        ]);
    }

    /**
     * Update the user's profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'u_email')->ignore($user->u_id, 'u_id')],
            'birthdate' => ['sometimes', 'date', 'before:today'],
            'current_password' => ['required_with:new_password', 'current_password'],
            'new_password' => ['sometimes', 'string', 'min:8'],
            'profile_picture' => ['sometimes', 'image', 'max:2048'] // max 2MB
        ]);

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            
            // Use database transaction to ensure atomic operation
            DB::beginTransaction();
            try {
                // Create new image record
                $image = new Image();
                $image->setImageContent(
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName(),
                    $file->getMimeType()
                );
                $image->save();
                
                // Update user's profile image ID
                $user->u_profileImageId = $image->img_id;
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'Failed to upload profile picture'], 500);
            }
        }

        if (isset($validated['name'])) {
            $user->u_name = $validated['name'];
        }
        
        if (isset($validated['email'])) {
            $user->u_email = $validated['email'];
        }
        
        if (isset($validated['birthdate'])) {
            $user->u_birthdate = $validated['birthdate'];
        }
        
        if (isset($validated['new_password'])) {
            $user->u_password = Hash::make($validated['new_password']);
        }

        $user->save();

        // Return user data without binary image data
        $response = [
            'message' => 'Profile updated successfully',
            'user' => [
                'u_name' => $user->u_name,
                'u_email' => $user->u_email,
                'u_birthdate' => $user->u_birthdate,
                'u_role' => $user->u_role,
                'profilePic' => $user->fresh('profileImage')->profileImage?->getDataUrl()
            ]
        ];

        return response()->json($response);
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