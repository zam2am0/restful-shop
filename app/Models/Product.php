<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'Name',
        'Description',
        'Price',
        'StockQuantity',
    ];
    //Relationships
    public function cartItems()
    {
        return $this->hasMany(CartItem::class); //One-to-Many: A cart can contain multiple cart items.
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class); //One-to-Many: A cart can contain multiple order items.
    }
}
