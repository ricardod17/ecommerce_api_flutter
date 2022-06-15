<?php

namespace App\Models;

use App\Traits\HasScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasScope;

    protected $fillable = [
        'name', 'description', 'price', 'categories_id', 'tags', 'users_id'
    ];

    public function gallery()
    {
        return $this->hasMany(ProductGallery::class, 'products_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'categories_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }
}
