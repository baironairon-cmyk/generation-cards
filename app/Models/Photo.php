<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $fillable = [
        'user_id',
        'original_path',
        'bg_removed_path',
        'generated_images',
        'product_card',
    ];

    protected $casts = [
        'generated_images' => 'array',
        'product_card' => 'array',
    ];
}
