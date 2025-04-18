<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameStoreController extends Controller
{
    public function getRandomFeaturedGames()
    {
        // Get 5 random games for the carousel
        return Game::inRandomOrder()
            ->with('developer')
            ->where('g_status', 'verified')
            ->take(5)
            ->get();
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
                $discount = $game->g_discount;
                return array_merge($game->toArray(), ['discount' => $discount]);
            });
    }

    public function getCategories()
    {
        // Get distinct categories from game_categories table
        return GameCategory::select('gc_category')
            ->distinct()
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->gc_category,
                    'icon' => $this->getCategoryIcon($category->gc_category)
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
            ->get();
    }

    public function getExploreProducts(Request $request)
    {
        return Game::with('developer')
            ->where('g_status', 'verified')
            ->inRandomOrder()
            ->take(10)
            ->get();
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
