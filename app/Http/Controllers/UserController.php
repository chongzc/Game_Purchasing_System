<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class UserController extends Controller
{
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
            $user = User::find(Auth::id());
            
            if (!$user) {
                return response()->json(['message' => 'User not authenticated'], 401);
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
    
            User::where('u_id', $user->u_id)->update([
                'u_name' => $user->u_name,
                'u_email' => $user->u_email,
                'u_birthdate' => $user->u_birthdate,
                'u_profileImagePath' => $user->u_profileImagePath,
                'u_password' => $user->u_password
            ]);
            
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
     * Get the authenticated user's profile information
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile()
    {
        try {
            // Get the authenticated user
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['message' => 'User not authenticated'], 401);
            }
            
            // Return user profile data
            return response()->json([
                'u_id' => $user->u_id,
                'u_name' => $user->u_name,
                'name' => $user->u_name,
                'u_email' => $user->u_email,
                'email' => $user->u_email,
                'u_birthdate' => $user->u_birthdate,
                'birthdate' => $user->u_birthdate,
                'u_role' => $user->u_role,
                'role' => $user->u_role,
                'profilePic' => $user->u_profileImagePath ? asset($user->u_profileImagePath) : null
            ]);
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Get profile error: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to retrieve profile: ' . $e->getMessage()
            ], 500);
        }
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
