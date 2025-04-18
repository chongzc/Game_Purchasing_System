<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display a listing of the user's cart items.
     */
    public function index()
    {
        $userId = Auth::id();
        $cartItems = Cart::where('c_userId', $userId)
            ->with('game')
            ->get();
            
        $total = $cartItems->sum('c_price');
        
        // Return JSON response for API requests
        if (request()->expectsJson()) {
            return response()->json([
                'cartItems' => $cartItems,
                'total' => $total
            ]);
        }
        
        // Return view for web requests
        return view('cart.index', compact('cartItems', 'total'));
    }

    /**
     * Add a game to the cart.
     */
    public function addToCart(Request $request)
    {
        // Debug auth status
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Not authenticated',
                'auth_status' => Auth::check(),
                'user_id' => Auth::id(),
                'request_headers' => $request->headers->all(),
                'session_id' => session()->getId(),
                'has_session' => session()->isStarted(),
            ], 401);
        }

        // Validate the game ID
        $validated = $request->validate([
            'gameId' => 'required|exists:games,g_id',
            'originalPrice' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
        ]);

        $userId = Auth::id();
        $gameId = $request->gameId;
        
        // Debug info for client
        $debugInfo = [
            'userId' => $userId,
            'gameId' => $gameId,
            'auth_check' => Auth::check(),
            'session_id' => session()->getId()
        ];
        
        // Check if the game is already in the cart
        $existingCartItem = Cart::where('c_userId', $userId)
            ->where('c_gameId', $gameId)
            ->first();
            
        if ($existingCartItem) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Game is already in your cart.',
                    'debug' => $debugInfo
                ]);
            }
            
            return redirect()->back()->with('message', 'Game is already in your cart.');
        }
        
        // Get the game data
        $game = Game::find($gameId);
        
        // If originalPrice is provided in the request, use it, otherwise use the game's price
        $price = $request->filled('originalPrice') ? $request->originalPrice : $game->g_price;
        
        // If discount is provided in the request, use it, otherwise use the game's discount
        $discount = $request->filled('discount') ? $request->discount : $game->g_discount;
        
        // Add to cart with original price (not discounted price)
        Cart::create([
            'c_userId' => $userId,
            'c_gameId' => $gameId,
            'c_price' => $price,
            'c_discount' => $discount,
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Game added to cart successfully.',
                'debug' => $debugInfo
            ]);
        }
        
        return redirect()->back()->with('message', 'Game added to cart successfully.');
    }

    /**
     * Remove a game from the cart.
     */
    public function removeFromCart($id)
    {
        $userId = Auth::id();
        
        // Special case for path confusion
        if ($id === 'clear') {
            return $this->clearCart();
        }
        
        $cartItem = Cart::where('id', $id)
            ->where('c_userId', $userId)
            ->first();
            
        if (!$cartItem) {
            if (request()->expectsJson()) {
                return response()->json([
                    'error' => 'Cart item not found.',
                    'id' => $id,
                    'user_id' => $userId
                ], 404);
            }
            
            return redirect()->back()->with('error', 'Cart item not found.');
        }
        
        $cartItem->delete();
        
        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Game removed from cart.'
            ]);
        }
        
        return redirect()->back()->with('message', 'Game removed from cart.');
    }

    /**
     * Clear all items from the cart.
     */
    public function clearCart()
    {
        $userId = Auth::id();
        
        // Check if user is authenticated
        if (!$userId) {
            if (request()->expectsJson()) {
                return response()->json([
                    'error' => 'Not authenticated',
                    'auth_status' => Auth::check(),
                    'debug' => [
                        'session_id' => session()->getId(),
                        'has_session' => session()->isStarted(),
                    ]
                ], 401);
            }
            
            return redirect()->route('login');
        }
        
        // Get count before deletion
        $count = Cart::where('c_userId', $userId)->count();
        
        // Delete all cart items
        Cart::where('c_userId', $userId)->delete();
        
        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Cart cleared successfully.',
                'deleted_count' => $count,
                'user_id' => $userId
            ]);
        }
        
        return redirect()->back()->with('message', 'Cart cleared successfully.');
    }

    /**
     * Checkout process.
     */
    public function checkout()
    {
        $userId = Auth::id();
        $cartItems = Cart::where('c_userId', $userId)
            ->with('game')
            ->get();
            
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }
        
        $total = $cartItems->sum('c_price');
        
        return view('cart.checkout', compact('cartItems', 'total'));
    }
}
