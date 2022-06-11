<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasScope;

class ProductGallery extends Model
{
    use HasFactory, SoftDeletes, HasScope;

    protected $fillable = [
        'users_id', 'products_id', 'url'
    ];

    public function products()
    {
        return $this->belongsTo(Product::class, 'products_id', 'id');
    }

    public function getUrlAttribute($url)
    {
        return config('app.url') . Storage::url($url);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }
}