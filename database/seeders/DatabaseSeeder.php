<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,           // First create users (including developers)
            GameSeeder::class,           // Then create games
            GameCategorySeeder::class,   // Then associate categories with games
            ImageSeeder::class,          // Then store game images
            PurchaseSeeder::class,       // Then create purchases
            ReviewSeeder::class,         // Then add reviews
            UserLibSeeder::class,        // Then populate user libraries
            WishlistSeeder::class        // Finally add wishlist items
        ]);
    }
}
