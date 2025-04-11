<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $table = 'games';

    protected $fillable = [
        'g_title',
        'g_description',
        'g_price',
        'g_discount',
        'g_developerId',
        'g_status',
        'g_owned',
        'g_mainImage',
        'g_exImg1',
        'g_exImg2',
        'g_exImg3',
        'g_overallRate',
        'g_language',
    ];

    protected $casts = [
        'g_price' => 'float',
        'g_discount' => 'float',
        'g_overallRate' => 'float',
    ];

    public function developer()
    {
        return $this->belongsTo(User::class, 'g_developerId');
    }

    public function purchasedUsers()
    {
        return $this->belongsToMany(User::class, 'purchases', 'p_gameId', 'p_user_id')
                    ->withTimestamps()
                    ->withPivot('p_gameName', 'p_purchaseDate', 'p_receiptNumber');
    }

    public function wishlistedUsers()
    {
        return $this->belongsToMany(User::class, 'wishlist', 'wl_gameId', 'wl_userId')
                    ->withTimestamps()
                    ->withPivot('wl_name');
    }

    public function libraryUsers()
    {
        return $this->belongsToMany(User::class, 'game_lib', 'gl_gameId', 'gl_userId')
                    ->withTimestamps()
                    ->withPivot('gl_name', 'gl_status');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'r_game_id');
    }

    public function categories()
    {
        return $this->belongsToMany(GameCategory::class, 'game_category', 'gc_game_id', 'gc_category_id');
    }
}
