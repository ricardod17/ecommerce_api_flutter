<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasScope;

class Transaction extends Model
{
    use HasFactory, SoftDeletes, HasScope;

    protected $fillable = ['users_id', 'address', 'payment', 'total_price', 'shipping_price', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'transactions_id', 'id');
    }

    // public static function boot()
    // {
    //     parent::boot();
    //     self::deleting(function ($transaction) {
    //         $transaction->items->delete();
    //     });
    // }
}