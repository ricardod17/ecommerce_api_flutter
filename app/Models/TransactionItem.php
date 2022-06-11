<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionItem extends Model
{
    use HasFactory;

    protected $primaryKey = "id";
    protected $table = 'transaction_items';
    protected $fillable = [
        'users_id', 'products_id', 'transactions_id', 'quantity'
    ];

    public function product()
    {
        return $this->hasOne(Product::class, 'id',  'products_id');
    }

    public function transaction()
    {
        return $this->belongsToMany(Transaction::class, 'transaction_items');
    }
}