<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'quantity', 'order_date', 'delivery_status'];
    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'order_details');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function orderDetail()
    {
        return $this->hasOne(OrderDetail::class);
    }
}
