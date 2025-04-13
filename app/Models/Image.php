<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $primaryKey = 'img_id';

    protected $fillable = [
        'img_filename',
        'img_filetype',
        'img_filesize'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = ['img_file'];

    /**
     * Get the base64 encoded image data with proper mime type
     */
    public function getDataUrl()
    {
        if (!$this->getOriginal('img_file')) {
            return null;
        }
        return "data:{$this->img_filetype};base64," . base64_encode($this->getOriginal('img_file'));
    }

    /**
     * Set the image file content
     */
    public function setImageContent($content, $filename, $mimeType = null)
    {
        $this->img_filename = $filename;
        $this->img_filetype = $mimeType ?: 'application/octet-stream';
        $this->img_filesize = strlen($content);
        
        // Use DB::raw to prevent Laravel from trying to JSON encode the binary data
        $this->attributes['img_file'] = $content;
        
        return $this;
    }
}
