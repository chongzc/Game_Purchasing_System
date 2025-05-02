<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\GameController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Gate;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Log;

Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

//Get method
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');

//Post method
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', function () {
    if (auth()->check() && !Gate::allows('is-user')) {
        abort(403, 'Unauthorized action.');
    }
    return view('application');
})->name('home');

Route::get('/purchase-history', function () {
    if (auth()->check() && !Gate::allows('is-user')) {
        abort(403, 'Unauthorized action.');
    }
    
    return view('application');
})->name('purchase-history');

Route::get('/game-store', function () {
    if (auth()->check() && !Gate::allows('is-user')) {
        abort(403, 'Unauthorized action.');
    }
    
    return view('application');
})->name('game-store');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // User routes
    Route::get('/cart', function () {
        if (!Gate::allows('is-user')) {
            abort(403, 'Unauthorized action.');
        }

        return view('application');
    })->name('cart');

    Route::get('/checkout', function () {
        if (!Gate::allows('is-user')) {
            abort(403, 'Unauthorized action.');
        }

        return view('application');
    })->name('checkout');

    Route::get('/user-library', function () {
        if (!Gate::allows('is-user')) {
            abort(403, 'Unauthorized action.');
        }

        return [GameController::class, 'library'];
    })->name('user-library');
    
    Route::get('/game-library/games', function () {
        if (!Gate::allows('is-user')) {
            abort(403, 'Unauthorized action.');
        }

        return [GameController::class, 'library'];
    });
    
    Route::get('/user-wishlist', function () {
        if (!Gate::allows('is-user')) {
            abort(403, 'Unauthorized action.');
        }

        return view('application');
    })->name('user-wishlist');

    // Developer routes
    Route::get('/developer-dashboard', function () {
        if (!Gate::allows('is-developer')) {
            abort(403, 'Unauthorized action.');
        }

        return view('application');
    })->name('developer-dashboard');

    Route::get('/create-game', function () {
        if (!Gate::allows('is-developer')) {
            abort(403, 'Unauthorized action.');
        }

        return view('application');
    })->name('create-game');

    // Admin routes
    Route::get('/admin-dashboard', function () {
        if (!Gate::allows('is-admin')) {
            abort(403, 'Unauthorized action.');
        }

        return view('application');
    })->name('admin-dashboard');
    
    Route::get('/admin/games', function () {
        if (!Gate::allows('is-admin')) {
            abort(403, 'Unauthorized action.');
        }

        return view('application');
    })->name('admin.games');
});

Route::get('/{any}', function () {
    return view('application');
})->where('any', '^(?!api).*$');

// Test route for S3 connection
Route::get('/test-s3-connection', function () {
    try {
        // Create an S3 client
        $s3Client = new S3Client([
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
            'verify' => false, // Disable SSL verification
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);
        
        // List buckets to test connection
        $buckets = $s3Client->listBuckets();
        
        // Check if the specified bucket exists
        $bucket = env('AWS_BUCKET');
        $bucketExists = false;
        
        foreach ($buckets['Buckets'] as $b) {
            if ($b['Name'] === $bucket) {
                $bucketExists = true;
                break;
            }
        }
        
        // Try to put a test object
        $testResult = null;
        if ($bucketExists) {
            $testResult = $s3Client->putObject([
                'Bucket' => $bucket,
                'Key'    => 'test-file.txt',
                'Body'   => 'This is a test file to verify S3 connection.',
                'ACL'    => 'public-read'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'S3 connection successful',
            'buckets' => $buckets['Buckets'],
            'bucket_exists' => $bucketExists,
            'specified_bucket' => $bucket,
            'test_put_result' => $testResult ? $testResult->toArray() : null,
            'aws_config' => [
                'region' => env('AWS_DEFAULT_REGION'),
                'bucket' => env('AWS_BUCKET'),
                'url' => env('AWS_URL'),
                'key_length' => strlen(env('AWS_ACCESS_KEY_ID')),
                'secret_length' => strlen(env('AWS_SECRET_ACCESS_KEY')),
            ]
        ]);
    } catch (\Exception $e) {
        Log::error('S3 Test Error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'S3 connection failed',
            'error' => $e->getMessage(),
            'aws_config' => [
                'region' => env('AWS_DEFAULT_REGION'),
                'bucket' => env('AWS_BUCKET'),
                'url' => env('AWS_URL'),
                'key_length' => strlen(env('AWS_ACCESS_KEY_ID')),
                'secret_length' => strlen(env('AWS_SECRET_ACCESS_KEY')),
            ]
        ]);
    }
});

// Test route for S3 file upload
Route::get('/test-s3-upload', function () {
    try {
        // Create sample image
        $width = 400;
        $height = 300;
        $image = imagecreatetruecolor($width, $height);
        
        // Add some color and text to make it identifiable
        $bg = imagecolorallocate($image, 255, 200, 200);
        $textColor = imagecolorallocate($image, 0, 0, 100);
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);
        imagestring($image, 5, 150, 150, 'Test Image ' . date('Y-m-d H:i:s'), $textColor);
        
        // Save the image to a temporary file
        $tempFilePath = storage_path('app/temp_test_image.png');
        imagepng($image, $tempFilePath);
        imagedestroy($image);
        
        // Create an S3 client
        $s3Client = new S3Client([
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'http' => [
                'verify' => false
            ]
        ]);
        
        // Generate a unique key for the test image
        $key = 'test/test_image_' . time() . '.png';
        
        // Read the file
        $fileContent = file_get_contents($tempFilePath);
        if (!$fileContent) {
            throw new \Exception("Could not read temporary test file");
        }
        
        // Upload to S3
        $result = $s3Client->putObject([
            'Bucket' => env('AWS_BUCKET'),
            'Key'    => $key,
            'Body'   => $fileContent,
            'ContentType' => 'image/png',
            'ACL'    => 'public-read'
        ]);
        
        // Get the URL
        $url = $result->get('ObjectURL');
        
        // Check if the object exists in S3
        $exists = $s3Client->doesObjectExist(env('AWS_BUCKET'), $key);
        
        // Clean up temporary file
        @unlink($tempFilePath);
        
        return response()->json([
            'success' => true,
            'message' => 'S3 upload test complete',
            'url' => $url,
            'key' => $key,
            'exists_in_s3' => $exists,
            'bucket' => env('AWS_BUCKET'),
            'region' => env('AWS_DEFAULT_REGION'),
            'results' => [
                'etag' => $result->get('ETag'),
                'version_id' => $result->get('VersionId'),
                'other_data' => $result->toArray()
            ],
            'check_this_url' => "Check this URL in your browser: <a href='$url' target='_blank'>View Uploaded Image</a>",
        ]);
    } catch (\Exception $e) {
        Log::error('S3 Upload Test Error: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'S3 upload test failed',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'aws_config' => [
                'region' => env('AWS_DEFAULT_REGION'),
                'bucket' => env('AWS_BUCKET'),
                'url' => env('AWS_URL'),
                'key_length' => strlen(env('AWS_ACCESS_KEY_ID')),
                'secret_length' => strlen(env('AWS_SECRET_ACCESS_KEY')),
            ]
        ]);
    }
});

// Check if a specific file exists in S3
Route::get('/check-s3-file', function (\Illuminate\Http\Request $request) {
    $key = $request->query('key'); // Get the object key from query parameter
    
    if (!$key) {
        return response()->json([
            'success' => false,
            'message' => 'No key parameter provided',
            'usage' => 'Add ?key=your_file_path to check if a specific file exists'
        ]);
    }
    
    try {
        // Create an S3 client
        $s3Client = new S3Client([
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
            'verify' => false, // Disable SSL verification
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'http' => [
                'verify' => false
            ],
        ]);
        
        // Check if the object exists
        $exists = $s3Client->doesObjectExist(env('AWS_BUCKET'), $key);
        
        if ($exists) {
            // If it exists, get the object URL
            $url = "https://" . env('AWS_BUCKET') . ".s3." . env('AWS_DEFAULT_REGION') . ".amazonaws.com/" . $key;
            
            // Try to get the object metadata
            $metadata = $s3Client->headObject([
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $key
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'File exists in S3',
                'key' => $key,
                'url' => $url,
                'metadata' => $metadata->toArray(),
                'link' => "<a href='$url' target='_blank'>View File</a>"
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'File does not exist in S3',
                'key' => $key,
                'bucket' => env('AWS_BUCKET'),
                'region' => env('AWS_DEFAULT_REGION'),
            ]);
        }
    } catch (\Exception $e) {
        Log::error('S3 File Check Error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error checking S3 file',
            'error' => $e->getMessage(),
            'key' => $key,
            'bucket' => env('AWS_BUCKET'),
            'region' => env('AWS_DEFAULT_REGION'),
        ]);
    }
});

// Direct S3 diagnostic test
Route::get('/s3-diagnostic', function () {
    $output = [
        'aws_config' => [
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'key_present' => !empty(env('AWS_ACCESS_KEY_ID')),
            'secret_present' => !empty(env('AWS_SECRET_ACCESS_KEY')),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
        ],
        'steps' => [],
        'test_file' => null,
    ];
    
    try {
        // Step 1: Create S3 client
        $output['steps'][] = ['step' => 'Creating S3 client', 'status' => 'started'];
        
        $s3Client = new S3Client([
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'http' => [
                'verify' => false
            ],
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'endpoint' => env('AWS_ENDPOINT'),
        ]);
        
        $output['steps'][count($output['steps'])-1]['status'] = 'completed';
        
        // Step 2: List buckets to check credentials
        $output['steps'][] = ['step' => 'Listing buckets to validate credentials', 'status' => 'started'];
        
        $buckets = $s3Client->listBuckets();
        $bucketExists = false;
        $output['buckets'] = [];
        
        foreach ($buckets['Buckets'] as $bucket) {
            $output['buckets'][] = $bucket['Name'];
            if ($bucket['Name'] === env('AWS_BUCKET')) {
                $bucketExists = true;
            }
        }
        
        $output['bucket_exists'] = $bucketExists;
        $output['steps'][count($output['steps'])-1]['status'] = 'completed';
        
        if (!$bucketExists) {
            $output['steps'][] = [
                'step' => 'Checking bucket existence',
                'status' => 'error',
                'message' => 'The specified bucket "' . env('AWS_BUCKET') . '" does not exist in your AWS account'
            ];
        } else {
            $output['steps'][] = [
                'step' => 'Checking bucket existence',
                'status' => 'completed',
                'message' => 'The specified bucket "' . env('AWS_BUCKET') . '" exists'
            ];
        }
        
        // Step 3: Create a small test file
        $output['steps'][] = ['step' => 'Creating test file', 'status' => 'started'];
        
        // Generate a simple text file with current timestamp
        $tempFileName = 'test_file_' . time() . '.txt';
        $tempFilePath = storage_path('app/' . $tempFileName);
        $fileContent = 'This is a test file created at ' . date('Y-m-d H:i:s');
        file_put_contents($tempFilePath, $fileContent);
        
        $output['steps'][count($output['steps'])-1]['status'] = 'completed';
        $output['steps'][count($output['steps'])-1]['file_path'] = $tempFilePath;
        
        // Step 4: Upload test file
        $output['steps'][] = ['step' => 'Uploading test file to S3', 'status' => 'started'];
        
        $key = 'test/diagnostic_' . time() . '.txt';
        
        $putObjectParams = [
            'Bucket' => env('AWS_BUCKET'),
            'Key' => $key,
            'Body' => $fileContent,
            'ContentType' => 'text/plain',
            'ACL' => 'public-read',
        ];
        
        $output['steps'][count($output['steps'])-1]['put_object_params'] = $putObjectParams;
        
        // Use a try-catch within this step to provide detailed error information
        try {
            $result = $s3Client->putObject($putObjectParams);
            
            $output['steps'][count($output['steps'])-1]['status'] = 'completed';
            $output['steps'][count($output['steps'])-1]['upload_result'] = [
                'success' => true,
                'url' => $result->get('ObjectURL'),
                'etag' => $result->get('ETag'),
            ];
            
            // Clean up the temporary file
            @unlink($tempFilePath);
            
            $output['test_file'] = [
                'key' => $key,
                'url' => $result->get('ObjectURL'),
                'success' => true,
            ];
            
            // Step 5: Verify the file was uploaded correctly
            $output['steps'][] = ['step' => 'Verifying file exists in S3', 'status' => 'started'];
            
            $exists = $s3Client->doesObjectExist(env('AWS_BUCKET'), $key);
            
            if ($exists) {
                $output['steps'][count($output['steps'])-1]['status'] = 'completed';
                $output['steps'][count($output['steps'])-1]['message'] = 'File exists in S3';
                
                // Try to get the object to verify content
                $getResult = $s3Client->getObject([
                    'Bucket' => env('AWS_BUCKET'),
                    'Key' => $key,
                ]);
                
                $retrievedContent = (string)$getResult['Body'];
                $output['steps'][count($output['steps'])-1]['content_match'] = ($retrievedContent === $fileContent);
            } else {
                $output['steps'][count($output['steps'])-1]['status'] = 'error';
                $output['steps'][count($output['steps'])-1]['message'] = 'File was not found in S3 after upload';
            }
        } catch (\Exception $e) {
            $output['steps'][count($output['steps'])-1]['status'] = 'error';
            $output['steps'][count($output['steps'])-1]['error'] = [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ];
            
            // Clean up the temporary file on error
            @unlink($tempFilePath);
        }
        
    } catch (\Exception $e) {
        $output['exception'] = [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ];
    }
    
    return response()->json($output);
});
