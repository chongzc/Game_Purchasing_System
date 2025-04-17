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

    protected $primaryKey = 'u_id';

    protected $fillable = [
        'u_name',
        'u_email',
        'u_password',
        'u_birthdate',
        'u_role',
        'u_profileImagePath',
    ];

    protected $hidden = [
        'u_password',
        'remember_token',
    ];

    protected $casts = [
        'u_birthdate' => 'date',
        'u_password' => 'hashed',
    ];

    /**
     * Get the email column for authentication.
     */
    public function getAuthIdentifierName()
    {
        return 'u_id';
    }

    /**
     * Get the username for login.
     */
    public function getUsernameAttribute()
    {
        return $this->u_name;
    }

    /**
     * Get the email for login.
     */
    public function getEmailAttribute()
    {
        return $this->u_email;
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->u_password;
    }

    /**
     * Get the user's profile image.
     */
    public function profileImage()
    {
        return $this->belongsTo(Image::class, 'u_profileImageId', 'img_id');
    }

    /**
     * Get the games published by the user (for developers).
     */
    public function publishedGames()
    {
        return $this->hasMany(Game::class, 'g_developerId', 'u_id');
    }

    /**
     * Get the games purchased by the user.
     */
    public function purchasedGames()
    {
        return $this->belongsToMany(Game::class, 'purchases', 'p_userId', 'p_gameId')
                    ->withPivot(['p_purchaseDate', 'p_purchasePrice', 'p_receiptNumber'])
                    ->withTimestamps();
    }

    /**
     * Get user's wishlist.
     */
    public function wishlist()
    {
        return $this->belongsToMany(Game::class, 'wishlist', 'wl_userId', 'wl_gameId')
                    ->withPivot('wl_name')
                    ->withTimestamps();
    }

    /**
     * Get the user's game library.
     */
    public function gameLibrary()
    {
        return $this->belongsToMany(Game::class, 'user_lib', 'ul_userId', 'ul_gameId')
                    ->withPivot(['ul_name', 'ul_status'])
                    ->withTimestamps();
    }

    /**
     * Get the reviews written by the user.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'r_userId', 'u_id');
    }

    /**
     * Check if the user is a customer.
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
