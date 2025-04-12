<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $table = 'games';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'g_id';

    protected $fillable = [
        'g_title',
        'g_description',
        'g_price',
        'g_discount',
        'g_developerId',
        'g_status',
        'g_downloadCount',
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

    /**
     * Get the developer that publish this game.
     */
    public function developer()
    {
        return $this->belongsTo(User::class, 'g_developerId');
    }

    /**
     * Get the user that owns this game.
     */
    public function purchasedByUsers()
    {
        return $this->belongsToMany(User::class, 'purchases', 'p_gameId', 'p_userId')
                    ->withTimestamps()
                    ->withPivot('p_gameName', 'p_purchaseDate', 'p_receiptNumber');
    }

    /**
     * Get users who have wishlisted this game.
     */
    public function wishlistedByUsers()
    {
        return $this->belongsToMany(User::class, 'wishlists', 'wl_gameId', 'wl_userId')
                    ->withTimestamps();
    }

    public function libraryUsers()
    {
        return $this->belongsToMany(User::class, 'user_lib', 'ul_gameId', 'ul_userId')
                    ->withTimestamps()
                    ->withPivot('ul_status');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'r_gameId');
    }

    public function categories()
    {
        return $this->belongsToMany(GameCategory::class, 'game_category', 'gc_gameId', 'gc_id');
    }
}
