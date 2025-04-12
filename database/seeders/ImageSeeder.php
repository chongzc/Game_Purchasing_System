<?php

namespace Database\Seeders;

use App\Models\Image;
use Illuminate\Database\Seeder;

class ImageSeeder extends Seeder
{
    public function run(): void
    {
        // Create dummy image data
        $dummyImageData = base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/4QAYRXhpZgAASUkqAAgAAAAAAAAAAAAAAP/hADhQaG90b3Nob3AgMy4wADhCSU0EBAAAAAAADxcBWgADGyVHHAIAAAIAAAA4QklNBCUAAAAAABDo8VzzL8EYoaJ7Z63FMET/2wBDAFA3PEY8MlBGQUZaVVBfeMiCeG5uePWvuZHI////////////////////////////////////////////////////2wBDAVVaWnhpeOuCguv/////////////////////////////////////////////////////////////////////////wAARCAAIAAgDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAX/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=');

        $images = [
            [
                'img_filename' => 'placeholder.jpg',
                'img_filetype' => 'jpg',
                'img_filesize' => strlen($dummyImageData),
                'img_file' => $dummyImageData
            ],
            [
                'img_filename' => 'game_background.jpg',
                'img_filetype' => 'jpg',
                'img_filesize' => strlen($dummyImageData),
                'img_file' => $dummyImageData
            ]
        ];

        foreach ($images as $image) {
            Image::create($image);
        }
    }
}
