<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLib extends Model
{
    use HasFactory;

    protected $table = 'user_lib';
    protected $primaryKey = 'ul_id';

    protected $fillable = [
        'ul_name',
        'ul_gameId',
        'ul_userId',
        'ul_status',
    ];

    protected $casts = [
        'ul_status' => 'string'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'ul_userId', 'u_id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'ul_gameId', 'g_id');
    }

    public function scopeActive($query)
    {
        return $query->where('ul_status', 'installed');
    }
}
