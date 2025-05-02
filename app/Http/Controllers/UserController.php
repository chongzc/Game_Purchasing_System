<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

// Add necessary imports for direct SDK usage
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

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
                    
                    $directory = 'user_profile'; // Define the directory
                    
                    // Delete previous profile picture if it exists using Storage
                    if ($user->u_profileImagePath) {
                        try {
                            // If it's an S3 URL, extract the key first
                            if (filter_var($user->u_profileImagePath, FILTER_VALIDATE_URL) && 
                                strpos($user->u_profileImagePath, 's3.') !== false) {
                                // Extract the key from the URL
                                $urlParts = parse_url($user->u_profileImagePath);
                                $path = ltrim($urlParts['path'], '/');
                                Log::info('Attempting to delete previous S3 file:', ['path' => $path]);
                                
                                // Delete directly using the S3 client
                                try {
                                    $s3Client = new S3Client([
                                        'region' => env('AWS_DEFAULT_REGION'),
                                        'version' => 'latest',
                                        'credentials' => [
                                            'key'    => env('AWS_ACCESS_KEY_ID'),
                                            'secret' => env('AWS_SECRET_ACCESS_KEY'),
                                        ],
                                        'http' => [
                                            'verify' => false // SSL verification disabled
                                        ]
                                    ]);
                                    
                                    $s3Client->deleteObject([
                                        'Bucket' => env('AWS_BUCKET'),
                                        'Key'    => $path
                                    ]);
                                    
                                    Log::info('Deleted previous S3 file successfully');
                                } catch (\Exception $e) {
                                    Log::warning('Error deleting previous S3 file: ' . $e->getMessage());
                                    // Continue anyway - don't let this stop the new upload
                                }
                            } else {
                                // For local storage
                                Log::info('Attempting to delete local file:', ['path' => $user->u_profileImagePath]);
                                if (Storage::exists($user->u_profileImagePath)) {
                                    Storage::delete($user->u_profileImagePath);
                                    Log::info('Deleted previous profile picture using Storage: ' . $user->u_profileImagePath);
                                }
                            }
                        } catch (\Exception $e) {
                            Log::warning('Error checking/deleting previous profile image: ' . $e->getMessage());
                            // Continue anyway - this shouldn't prevent the new upload
                        }
                    }
                    
                    // --- S3 UPLOAD PROCESS --- //
                    $s3UploadSuccess = false;
                    $s3ErrorMessage = '';
                    
                    try {
                        // Create a simpler S3 client configuration
                        $s3Client = new S3Client([
                            'region' => env('AWS_DEFAULT_REGION'),
                            'version' => 'latest',
                            'credentials' => [
                                'key'    => env('AWS_ACCESS_KEY_ID'),
                                'secret' => env('AWS_SECRET_ACCESS_KEY'),
                            ],
                            'http' => [
                                'verify' => false // SSL verification disabled
                            ]
                        ]);

                        Log::info('AWS Credentials', [
                            'region' => env('AWS_DEFAULT_REGION'),
                            'bucket' => env('AWS_BUCKET'),
                            'key_length' => strlen(env('AWS_ACCESS_KEY_ID')),
                            'secret_length' => strlen(env('AWS_SECRET_ACCESS_KEY')),
                        ]);

                        $key = $directory . '/' . Str::uuid() . '.' . $file->getClientOriginalExtension();
                        
                        // Don't check for existence first - just upload
                        try {
                            $fileContent = file_get_contents($file->getRealPath());
                            
                            if (!$fileContent) {
                                throw new \Exception('Could not read file contents');
                            }
                            
                            Log::info('Attempting direct S3 upload', [
                                'bucket' => env('AWS_BUCKET'), 
                                'key' => $key, 
                                'file_size' => strlen($fileContent),
                                'mime_type' => $file->getMimeType()
                            ]);

                            // Try uploading with explicit Body instead of SourceFile
                            $result = $s3Client->putObject([
                                'Bucket' => env('AWS_BUCKET'),
                                'Key'    => $key,
                                'Body'   => $fileContent,
                                'ContentType' => $file->getMimeType()
                            ]);

                            Log::info('Direct S3 PutObject Result: Success', ['result_data' => $result->toArray()]);
                            
                            // Get the full object URL from S3
                            $objectUrl = $result->get('ObjectURL');
                            Log::info('S3 Object URL:', ['url' => $objectUrl]);
                            
                            // Store either the full URL or the path depending on availability
                            if ($objectUrl) {
                                // Store the full URL directly
                                $user->u_profileImagePath = $objectUrl;
                                Log::info('Storing full S3 URL as path', ['path' => $objectUrl]);
                            } else {
                                // Construct the URL from components
                                $bucket = env('AWS_BUCKET');
                                $region = env('AWS_DEFAULT_REGION');
                                $user->u_profileImagePath = "https://{$bucket}.s3.{$region}.amazonaws.com/{$key}";
                                Log::info('Constructed and storing S3 URL', ['path' => $user->u_profileImagePath]);
                            }
                            
                            $s3UploadSuccess = true;
                        } catch (\Exception $uploadEx) {
                            Log::error('Error during S3 putObject operation: ' . $uploadEx->getMessage());
                            throw $uploadEx; // Re-throw to be caught by the outer catch
                        }

                    } catch (AwsException $e) {
                        // Output detailed AWS error message
                        Log::error('Direct S3 Upload Error: ' . $e->getMessage());
                        Log::error('AWS Error Code: ' . $e->getAwsErrorCode());
                        Log::error('AWS Error Type: ' . $e->getAwsErrorType());
                        Log::error('AWS Request ID: ' . $e->getAwsRequestId());
                        Log::error('Full AWS Exception: ' . $e); // Log the full exception object for more detail
                        $s3UploadSuccess = false;
                        $s3ErrorMessage = 'AWS S3 Error: ' . $e->getMessage();
                    } catch (\Exception $e) {
                        Log::error('Generic Error during Direct S3 Upload: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
                        $s3UploadSuccess = false;
                        $s3ErrorMessage = 'Upload Error: ' . $e->getMessage();
                    }
                    // --- END S3 UPLOAD PROCESS --- //
                    
                    // If S3 upload failed, fall back to local storage
                    if (!$s3UploadSuccess) {
                        Log::info('S3 upload failed, falling back to local storage. Error: ' . $s3ErrorMessage);
                        
                        try {
                            // Store locally - use the public disk explicitly
                            $localPath = $file->store($directory, 'public');
                            Log::info('File stored locally at path: ' . $localPath);
                            
                            if ($localPath) {
                                $user->u_profileImagePath = $localPath;
                                Log::info('Profile path set to local storage: ' . $localPath);
                            } else {
                                Log::error('Local storage failed as well - no path returned');
                                throw new \Exception('Failed to store file locally after S3 upload failed: No path returned');
                            }
                        } catch (\Exception $e) {
                            Log::error('Local storage error: ' . $e->getMessage());
                            // Keep the previous profile image if both storage methods failed
                            // Return a specific error to the user
                            return response()->json([
                                'message' => 'Failed to upload profile picture: ' . $e->getMessage()
                            ], 500);
                        }
                    }
                    
                    // Log final profile path
                    Log::info('Final profile image path:', ['path_value' => $user->u_profileImagePath]);

                } catch (\Exception $e) {
                    Log::error('File upload block error (outer catch): ' . $e->getMessage());
                    Log::error('Stack trace: ' . $e->getTraceAsString());
                    
                    // Return the error response for the file upload
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
            
            // Log successful update with profile image path
            Log::info('Profile updated for user ID: ' . $user->u_id, [
                'profileImagePath' => $user->u_profileImagePath
            ]);
    
            // Return response with success message and user data
            $profilePicUrl = $this->getProfilePicUrl($user->u_profileImagePath);
            
            Log::info('Final profile picture URL being returned:', ['url' => $profilePicUrl]);
            
            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => [
                    'u_name' => $user->u_name,
                    'u_email' => $user->u_email,
                    'u_birthdate' => $user->u_birthdate,
                    'u_role' => $user->u_role,
                    'role' => $user->u_role,
                    'profilePic' => $profilePicUrl
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
                'profilePic' => $this->getProfilePicUrl($user->u_profileImagePath)
            ]);
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Get profile error: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to retrieve profile: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper method to get the complete profile picture URL
    private function getProfilePicUrl($path)
    {
        if (!$path) {
            Log::info('getProfilePicUrl: Path is null or empty');
            return null;
        }
        
        Log::info('getProfilePicUrl: Processing path', ['path' => $path]);
        
        // If it's already a complete URL, return it
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            Log::info('getProfilePicUrl: Path is already a URL');
            return $path;
        }
        
        // If it looks like a local storage path (starts with user_profile/), construct S3 URL
        if (Str::startsWith($path, 'user_profile/')) {
            $bucket = env('AWS_BUCKET');
            $region = env('AWS_DEFAULT_REGION');
            $url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
            Log::info('getProfilePicUrl: Constructed S3 URL', ['url' => $url]);
            return $url;
        }
        
        // For local storage paths
        $url = asset('storage/' . $path);
        Log::info('getProfilePicUrl: Local storage URL', ['url' => $url]);
        return $url;
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
                    'profilePic' => $this->getProfilePicUrl($user->u_profileImagePath),
                    'created_at' => $user->created_at
                ];
            });

        return response()->json($users);
    }

    /**
     * Utility method to check if a user's profile picture exists in S3
     */
    public function checkProfilePicture($userId = null)
    {
        try {
            // Default to authenticated user if no ID is provided
            if (!$userId) {
                $user = Auth::user();
                if (!$user) {
                    return response()->json(['message' => 'Not authenticated'], 401);
                }
            } else {
                $user = User::find($userId);
                if (!$user) {
                    return response()->json(['message' => 'User not found'], 404);
                }
            }
            
            // Check if user has a profile picture path
            if (!$user->u_profileImagePath) {
                return response()->json([
                    'message' => 'User has no profile picture path',
                    'user_id' => $user->u_id,
                    'username' => $user->u_name
                ]);
            }
            
            // Analyze the path
            $path = $user->u_profileImagePath;
            $isUrl = filter_var($path, FILTER_VALIDATE_URL);
            $s3Key = null;
            
            // Extract S3 key if it's a URL
            if ($isUrl && strpos($path, 's3.') !== false) {
                $urlParts = parse_url($path);
                $s3Key = ltrim($urlParts['path'], '/');
            } else if (strpos($path, 'user_profile/') === 0) {
                // It's already an S3 key
                $s3Key = $path;
            }
            
            $verificationResults = [];
            
            // Check if it's a valid S3 key
            if ($s3Key) {
                try {
                    $s3Client = new S3Client([
                        'region' => env('AWS_DEFAULT_REGION'),
                        'version' => 'latest',
                        'verify' => false,
                        'credentials' => [
                            'key'    => env('AWS_ACCESS_KEY_ID'),
                            'secret' => env('AWS_SECRET_ACCESS_KEY'),
                        ],
                    ]);
                    
                    // Check if the object exists
                    $exists = $s3Client->doesObjectExist(env('AWS_BUCKET'), $s3Key);
                    $verificationResults['exists_in_s3'] = $exists;
                    
                    if ($exists) {
                        // If it exists, get additional details
                        $metadata = $s3Client->headObject([
                            'Bucket' => env('AWS_BUCKET'),
                            'Key' => $s3Key
                        ]);
                        
                        $verificationResults['metadata'] = $metadata->toArray();
                        
                        // Construct a public URL
                        $publicUrl = "https://" . env('AWS_BUCKET') . ".s3." . env('AWS_DEFAULT_REGION') . ".amazonaws.com/" . $s3Key;
                        $verificationResults['public_url'] = $publicUrl;
                    } else {
                        // Try to upload a default image if the file doesn't exist
                        $verificationResults['attempted_recovery'] = $this->attemptRecoveryUpload($user, $s3Key);
                    }
                } catch (\Exception $e) {
                    $verificationResults['s3_check_error'] = $e->getMessage();
                }
            }
            
            return response()->json([
                'message' => 'Profile picture verification complete',
                'user_id' => $user->u_id,
                'username' => $user->u_name,
                'profile_path' => $path,
                'is_url' => $isUrl,
                'extracted_s3_key' => $s3Key,
                'verification_results' => $verificationResults,
                'profile_url_from_path' => $this->getProfilePicUrl($path)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error verifying profile picture',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Try to upload a default image for recovery
     */
    private function attemptRecoveryUpload($user, $targetKey)
    {
        try {
            // Create a default image
            $width = 200;
            $height = 200;
            $image = imagecreatetruecolor($width, $height);
            
            // Light blue background
            $bg = imagecolorallocate($image, 200, 200, 240);
            imagefilledrectangle($image, 0, 0, $width, $height, $bg);
            
            // Add initials if we have a name
            if ($user->u_name) {
                $textColor = imagecolorallocate($image, 60, 60, 100);
                $initials = strtoupper(substr($user->u_name, 0, 1));
                
                // Center the text
                $font = 5; // Large built-in font
                $textWidth = imagefontwidth($font) * strlen($initials);
                $textHeight = imagefontheight($font);
                $x = ($width - $textWidth) / 2;
                $y = ($height - $textHeight) / 2;
                
                imagestring($image, $font, $x, $y, $initials, $textColor);
            }
            
            // Save to temp file
            $tempFile = storage_path('app/temp_profile_' . time() . '.png');
            imagepng($image, $tempFile);
            imagedestroy($image);
            
            // Upload to S3
            $s3Client = new S3Client([
                'region' => env('AWS_DEFAULT_REGION'),
                'version' => 'latest',
                'verify' => false,
                'credentials' => [
                    'key'    => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
                'http' => [
                    'verify' => false
                ]
            ]);
            
            $fileContent = file_get_contents($tempFile);
            
            $result = $s3Client->putObject([
                'Bucket' => env('AWS_BUCKET'),
                'Key'    => $targetKey,
                'Body'   => $fileContent,
                'ContentType' => 'image/png',
                'ACL'    => 'public-read'
            ]);
            
            // Delete temp file
            @unlink($tempFile);
            
            return [
                'success' => true,
                'message' => 'Generated and uploaded recovery image',
                'url' => $result->get('ObjectURL')
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to generate recovery image',
                'error' => $e->getMessage()
            ];
        }
    }
}
