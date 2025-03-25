<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'quantity', 'image'];

    // Relation with Order model
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

    // Method to calculate total sales and quantity sold for a product
    public function getTotalSalesAndQuantity()
    {
        // Calculate total quantity sold and total sales (quantity * price)
        $totalQuantity = $this->orders()->sum('order_product.quantity');
        $totalSales = $this->orders()->sum(DB::raw('order_product.quantity * order_product.price'));

        return [
            'total_quantity' => $totalQuantity,
            'total_sales' => $totalSales
        ];
    }
}
