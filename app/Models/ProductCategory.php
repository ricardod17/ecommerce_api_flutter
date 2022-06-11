<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasScope;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes, HasScope;

    protected $fillable = [
        'users_id', 'name',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'categories_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }
}