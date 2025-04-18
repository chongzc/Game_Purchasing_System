<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    /**
     * Store a newly created purchase in storage.
     */
    public function store(Request $request)
    {
        try {
            // Get authenticated user ID
            $userId = Auth::id();
            
            if (!$userId) {
                return response()->json([
                    'message' => 'User not authenticated',
                    'auth_status' => Auth::check(),
                ], 401);
            }
            
            // Check if we received a batch of purchases
            if ($request->has('purchases') && is_array($request->purchases)) {
                $successCount = 0;
                $purchases = [];
                $baseReceiptNumber = null;
                
                // Process each purchase in the batch
                foreach ($request->purchases as $index => $purchaseData) {
                    // Validate individual purchase
                    if (empty($purchaseData['p_gameId']) || 
                        empty($purchaseData['p_purchasePrice']) || 
                        empty($purchaseData['p_purchaseDate']) || 
                        empty($purchaseData['p_receiptNumber']) ||
                        empty($purchaseData['p_gameName'])) {
                        continue; // Skip invalid records
                    }
                    
                    // Save the base receipt number for response
                    if ($baseReceiptNumber === null) {
                        $baseReceiptNumber = $purchaseData['p_receiptNumber'];
                    }
                    
                    // Modify receipt number to make it unique for each item
                    // but still allow grouping by base receipt number
                    $uniqueReceiptNumber = $purchaseData['p_receiptNumber'] . '-' . ($index + 1);
                    
                    // Create purchase record with user ID
                    $purchase = Purchase::create([
                        'p_userId' => $userId,
                        'p_gameId' => $purchaseData['p_gameId'],
                        'p_gameName' => $purchaseData['p_gameName'],
                        'p_purchasePrice' => $purchaseData['p_purchasePrice'],
                        'p_purchaseDate' => $purchaseData['p_purchaseDate'],
                        'p_receiptNumber' => $uniqueReceiptNumber
                    ]);
                    
                    if ($purchase) {
                        $successCount++;
                        $purchases[] = $purchase;
                    }
                }
                
                return response()->json([
                    'message' => $successCount . ' purchases recorded successfully',
                    'receiptNumber' => $baseReceiptNumber,
                    'purchases' => $purchases,
                ], 201);
            } else {
                // Handle single purchase record (original functionality)
                // Validate the incoming request
                $validated = $request->validate([
                    'p_gameId' => 'required',
                    'p_purchasePrice' => 'required|numeric',
                    'p_purchaseDate' => 'required|date',
                    'p_receiptNumber' => 'required|string',
                    'p_gameName' => 'required|string',
                ]);
                
                // Create the purchase record
                $purchase = Purchase::create([
                    'p_userId' => $userId,
                    'p_gameId' => $validated['p_gameId'],
                    'p_gameName' => $validated['p_gameName'],
                    'p_purchasePrice' => $validated['p_purchasePrice'],
                    'p_purchaseDate' => $validated['p_purchaseDate'],
                    'p_receiptNumber' => $validated['p_receiptNumber'],
                ]);
                
                return response()->json([
                    'message' => 'Purchase recorded successfully',
                    'purchase' => $purchase,
                ], 201);
            }
        } catch (\Exception $e) {
            Log::error('Purchase creation error: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to record purchase',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
    
    /**
     * Get purchases for the authenticated user grouped by receipt number.
     */
    public function getUserPurchases()
    {
        $userId = Auth::id();
        
        if (!$userId) {
            return response()->json([
                'message' => 'User not authenticated',
            ], 401);
        }
        
        // Get all user purchases
        $purchases = Purchase::where('p_userId', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Group purchases by receipt number (removing the item suffix)
        $groupedPurchases = $purchases->map(function($item) {
            // Extract base receipt number by removing the item suffix
            $baseReceiptNumber = preg_replace('/-\d+$/', '', $item->p_receiptNumber);
            
            // Add the base receipt number to the item
            $item->baseReceiptNumber = $baseReceiptNumber;
            return $item;
        })->groupBy('baseReceiptNumber');
        
        return response()->json([
            'purchases' => $groupedPurchases,
        ]);
    }
}
