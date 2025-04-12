<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $primaryKey = 'img_id';

    protected $fillable = [
        'img_file',
        'img_filename',
        'img_filetype',
        'img_filesize'
    ];
}
