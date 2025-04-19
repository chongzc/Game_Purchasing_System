<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GamePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can add a game to cart.
     */
    public function addToCart(User $user, Game $game): bool
    {
        // Only customers (role = user) can add to cart
        return $user->u_role === 'user' && !$user->isBanned();
    }

    /**
     * Determine whether the user can purchase a game directly.
     */
    public function purchase(User $user, Game $game): bool
    {
        // Only customers (role = user) can purchase games
        return $user->u_role === 'user' && !$user->isBanned();
    }

    /**
     * Determine whether the user can purchase any game.
     * This is called when Game::class is passed instead of a specific game instance.
     */
    public function purchaseAny(User $user): bool
    {
        // Only customers (role = user) can purchase games
        return $user->u_role === 'user' && !$user->isBanned();
    }

    /**
     * Determine whether the user can review a game.
     */
    public function review(User $user, Game $game): bool
    {
        // Only customers (role = user) can review games
        return $user->u_role === 'user' && !$user->isBanned();
    }
} 
