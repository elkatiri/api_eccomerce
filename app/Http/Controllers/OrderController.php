<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{


    public function shippedCount()
    {
        $shippedOrdersCount = Order::where('delivery_status', 'shipped')->count();
        return response()->json(['count' => $shippedOrdersCount]);
    }

    public function pendingCount()
    {
        $pendingOrdersCount = Order::where('delivery_status', 'pending')->count();
        return response()->json(['count' => $pendingOrdersCount]);
    }
    // Show all orders with their associated products
    public function index()
    {
        // Eager load products using the correct relationship 'products' (plural)
        return response()->json(Order::with('products','customer')->get());
    }

    // Store a new order
    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',  // Ensure the customer exists
            'order_date' => 'required|date',
            'delivery_status' => 'required|in:pending,shipped,delivered', // Delivery status validation
            'products' => 'required|array',  // Products should be an array
            'products.*.id' => 'required|exists:products,id',  // Ensure product exists
            'products.*.quantity' => 'required|integer|min:1',  // Ensure quantity is valid
        ]);

        // Create the new order
        $order = Order::create([
            'customer_id' => $validated['customer_id'],
            'order_date' => $validated['order_date'],
            'delivery_status' => $validated['delivery_status'],
        ]);

        // Attach products to the order (using the pivot table 'order_product')
        foreach ($validated['products'] as $product) {
            $order->products()->attach($product['id'], [
                'quantity' => $product['quantity'],
                'price' => \App\Models\Product::find($product['id'])->price,  // Get the price of the product
            ]);
        }

        // Return the created order as a JSON response
        return response()->json($order, 201);
    }

    // Show a specific order with its associated products
    public function show(Order $order)
    {
        // Load the products associated with the order
        return response()->json($order->load('products'));
    }

    // Update an existing order
    public function update(Request $request, Order $order)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'order_date' => 'nullable|date',
            'delivery_status' => 'nullable|in:pending,shipped,delivered', 
            'products' => 'nullable|array',
            'products.*.id' => 'nullable|exists:products,id',
            'products.*.quantity' => 'nullable|integer|min:1',
        ]);

        // Update the order details
        $order->update([
            'order_date' => $validated['order_date'] ?? $order->order_date,
            'delivery_status' => $validated['delivery_status'] ?? $order->delivery_status,
        ]);

        // Update the products if provided
        if (isset($validated['products'])) {
            foreach ($validated['products'] as $product) {
                // Update or attach the product to the order
                $order->products()->updateExistingPivot($product['id'], [
                    'quantity' => $product['quantity'],
                    'price' => \App\Models\Product::find($product['id'])->price,
                ]);
            }
        }

        // Return the updated order as a JSON response
        return response()->json($order);
    }

    // Delete a specific order
    public function destroy(Order $order)
    {
        // Delete the order and automatically remove associated pivot table records
        $order->delete();

        // Return a success message
        return response()->json(['message' => 'Order deleted successfully']);
    }
}
