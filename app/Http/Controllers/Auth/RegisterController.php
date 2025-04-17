<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('application');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255', 'unique:users,u_name'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,u_email'],
            'password' => ['required', 'string', 'min:8'],
            'birthDate' => ['required', 'date', 'before:today'],
            'role' => ['required', 'in:user,developer,admin'],
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'u_name' => $request->username,
            'u_email' => $request->email,
            'u_password' => Hash::make($request->password),
            'u_birthdate' => $request->birthDate,
            'u_role' => strtolower($request->role),
        ]);

        // Login the user directly with the appropriate credentials
        Auth::login($user);
        
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
                'message' => 'Registration successful'
            ]);
        }

        return redirect($redirectUrl);
    }
} 
