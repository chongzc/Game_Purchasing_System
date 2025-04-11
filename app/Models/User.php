<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'u_name',
        'u_email',
        'u_password',
        'u_birthdate',
        'u_role',
        'u_profilePic',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array   //what is these for
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'age' => 'integer',
        ];
    }

    /**
     * Get the username for authentication.
     */
    public function getAuthIdentifierName()
    {
        return 'u_name';
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->u_password;
    }

    /**
     * Get the games published by the user (for developers).
     */
    public function publishedGames()
    {
        return $this->hasMany(Game::class, 'g_developerId');
    }

    /**
     * Get the games purchased by the user.
     */
    public function purchasedGames()
    {
        return $this->belongsToMany(Game::class, 'purchases', 'p_userId', 'p_gameId')
                    ->withPivot('p_purchaseDate', 'p_price')
                    ->withTimestamps();
    }

    /**
     * Get user's wishlist.
     */
    public function wishlist()
    {
        return $this->belongsToMany(Game::class, 'wishlists', 'wl_userId', 'wl_gameId')
                    ->withTimestamps()
                    ->withPivot('aaa'); //apa tu 'aaa'
    }

    /**
     * Get the user's game library.
     */
    public function gameLibrary()
    {
        return $this->belongsToMany(Game::class, 'game_lib', 'gl_userId', 'gl_gameId')
                    ->withTimestamps()
                    ->withPivot('gl_name', 'gl_status');
    }

    /**
     * Get the reviews written by the user.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'r_userId', 'r_id');
    }

    /**
     * Check if the user is a normal user.
     */
    public function isCustomer(): bool
    {
        return $this->u_role === 'user';
    }

    /**
     * Check if the user is a developer.
     */
    public function isDeveloper(): bool
    {
        return $this->u_role === 'developer';
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->u_role === 'admin';
    }

    /**
     * Scope a query to only include users of a specific role.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $role
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('u_role', $role);
    }
}
