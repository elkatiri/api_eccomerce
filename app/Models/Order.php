<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_date',
        'delivery_status',
        'total_price',
    ];

    protected $casts = [
        'order_date' => 'datetime',
    ];

    // Relation avec Customer (un client peut avoir plusieurs commandes)
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

        // Relation avec Product (une commande peut avoir plusieurs produits)
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_product')
                    ->withPivot('quantity', 'price')  // Additional fields in the pivot table
                    ->withTimestamps();
    }


    // Calcul du prix total de la commande
    public function calculateTotalPrice()
    {
        $total = 0;

        foreach ($this->products as $product) {
            $total += $product->pivot->price * $product->pivot->quantity;
        }

        return $total;
    }

    // Sauvegarde automatique du prix total lors de la création ou mise à jour de la commande
    public static function boot()
    {
        parent::boot();

        static::saving(function ($order) {
            $order->total_price = $order->calculateTotalPrice();
        });
    }
}
