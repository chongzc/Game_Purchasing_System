<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('application');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Map the credentials to the database column names
        $authCredentials = [
            'u_email' => $credentials['email'],
            'password' => $credentials['password'],
        ];

        if (Auth::attempt($authCredentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Determine redirect based on role
            $redirectUrl = '/game-store';
            if ($user->isDeveloper()) {
                $redirectUrl = '/developer-dashboard';
            } elseif ($user->isAdmin()) {
                $redirectUrl = '/admin-dashboard';
            }
            
            if ($request->wantsJson()) {
                return response()->json([
                    'user' => $user,
                    'redirect' => $redirectUrl,
                    'message' => 'Login successful'
                ]);
            }

            return redirect()->intended($redirectUrl);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'The provided credentials do not match our records.'
            ], 422);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Logged out successfully']);
        }

        return redirect('/game-store');
    }
    
    /**
     * Get the authenticated user's information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getUser(Request $request)
    {
        if (Auth::check()) {
            return response()->json([
                'user' => Auth::user(),
                'isLoggedIn' => true
            ]);
        }
        
        return response()->json([
            'user' => null,
            'isLoggedIn' => false
        ]);
    }
} 
