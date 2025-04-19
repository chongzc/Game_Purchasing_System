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
            UserSeeder::class,           // First create users (including admin and developers)
            GameSeeder::class,           // Then create games
            PurchaseSeeder::class,       // Then create purchases
            ReviewSeeder::class,         // Then add reviews
            UserLibSeeder::class,        // Then populate user libraries
            WishlistSeeder::class        // Finally add wishlist items
        ]);
    }
}
