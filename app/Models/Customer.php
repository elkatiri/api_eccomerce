<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'password'];

    protected $hidden = ['password'];

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_details');
    }
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
