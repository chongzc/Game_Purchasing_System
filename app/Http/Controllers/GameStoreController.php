<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GameStoreController extends Controller
{
    public function getRandomFeaturedGames()
    {
        // Get 5 random games for the carousel
        return Game::inRandomOrder()
            ->with('developer')
            ->where('g_status', 'verified')
            ->take(5)
            ->get()
            ->map(function ($game) {
                return [
                    'g_id' => $game->g_id,
                    'g_title' => $game->g_title,
                    'g_description' => $game->g_description,
                    'g_price' => $game->g_price,
                    'g_discount' => $game->g_discount,
                    'mainImage' => $this->getImageUrl($game->g_mainImage)
                ];
            });
    }

    public function getFlashSales()
    {
        // Get 4 games with highest discount percentage
        return Game::select('*')
            ->with('developer')
            ->where('g_discount', '>', 0)
            ->where('g_status', 'verified')
            ->orderBy('g_discount', 'desc')
            ->take(5)
            ->get()
            ->map(function ($game) {
                return [
                    'g_id' => $game->g_id,
                    'g_title' => $game->g_title,
                    'g_price' => $game->g_price,
                    'g_discount' => $game->g_discount,
                    'g_mainImage' => $this->getImageUrl($game->g_mainImage),
                    'discount' => $game->g_discount
                ];
            });
    }

    public function getCategories()
    {
        // Get distinct categories from game_categories table
        return Game::select('g_category')
            ->where('g_status', 'verified')
            ->distinct()
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->g_category,
                    'icon' => $this->getCategoryIcon($category->g_category)
                ];
            });
    }

    public function getBestSelling()
    {
        // Get 4 games with highest ratings
        return Game::select('games.*')
            ->with('developer')
            ->where('g_status', 'verified')
            ->orderBy('g_overallRate', 'desc')
            ->take(5)
            ->get()
            ->map(function ($game) {
                return [
                    'g_id' => $game->g_id,
                    'g_title' => $game->g_title,
                    'g_price' => $game->g_price,
                    'g_discount' => $game->g_discount,
                    'g_overallRate' => $game->g_overallRate,
                    'g_mainImage' => $this->getImageUrl($game->g_mainImage)
                ];
            });
    }

    public function getExploreProducts(Request $request)
    {
        return Game::with('developer')
            ->where('g_status', 'verified')
            ->inRandomOrder()
            ->take(10)
            ->get()
            ->map(function ($game) {
                return [
                    'g_id' => $game->g_id,
                    'g_title' => $game->g_title,
                    'g_price' => $game->g_price,
                    'g_discount' => $game->g_discount,
                    'g_overallRate' => $game->g_overallRate,
                    'g_mainImage' => $this->getImageUrl($game->g_mainImage)
                ];
            });
    }

    /**
     * Helper function to get the full URL for a game image
     */
    private function getImageUrl($path)
    {
        if (!$path) {
            Log::info('getImageUrl: Path is null or empty');
            return null;
        }
        
        Log::info('getImageUrl: Processing path', ['path' => $path]);
        
        // If it's already a complete URL, return it
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            Log::info('getImageUrl: Path is already a URL');
            return $path;
        }
        
        // If it looks like an S3 key (starts with games/), construct S3 URL
        if (strpos($path, 'games/') === 0) {
            $bucket = env('AWS_BUCKET');
            $region = env('AWS_DEFAULT_REGION');
            $url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
            Log::info('getImageUrl: Constructed S3 URL', ['url' => $url]);
            return $url;
        }
        
        // For local storage paths
        $url = asset('storage/' . $path);
        Log::info('getImageUrl: Local storage URL', ['url' => $url]);
        return $url;
    }

    private function getCategoryIcon($category)
    {
        $icons = [
            'Action' => 'bx-run',
            'Adventure' => 'bx-compass',
            'RPG' => 'bx-diamond',
            'Strategy' => 'bx-brain',
            'Sports' => 'bx-baseball',
            'Simulation' => 'bx-cube-alt',
            'Racing' => 'bx-tachometer',
            'Puzzle' => 'bx-grid-alt',
            'Horror' => 'bx-ghost',
            'Fighting' => 'bxs-shield',
            // Add more mappings as needed
        ];

        return $icons[$category] ?? 'bx-game'; // Default icon if category not found
    }
} 
