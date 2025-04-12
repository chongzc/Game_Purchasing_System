<?php

namespace Database\Seeders;

use App\Models\UserLib;
use App\Models\Purchase;
use Illuminate\Database\Seeder;

class UserLibSeeder extends Seeder
{
    public function run(): void
    {
        // Add all purchased games to user libraries
        $purchases = Purchase::all();

        foreach ($purchases as $purchase) {
            UserLib::create([
                'ul_name' => $purchase->p_gameName,
                'ul_gameId' => $purchase->p_gameId,
                'ul_userId' => $purchase->p_userId,
                'ul_status' => 'installed' // All purchased games start as installed
            ]);
        }
    }
}
