<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{ 
    /**
     * Get sales by month and by day.
     * Returns total sales grouped by month and day.
     */
    public function salesByMonthAndDay()
    {
        // Get total sales grouped by month and year, ordered by latest
        $salesByMonth = Order::selectRaw('YEAR(order_date) as year, MONTH(order_date) as month, SUM(total_price) as total_sales')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Get total sales grouped by day, ordered by latest
        $salesByDay = Order::selectRaw('DATE(order_date) as day, SUM(total_price) as total_sales')
            ->groupBy('day')
            ->orderBy('day', 'desc')
            ->get();

        // Return both sales data as JSON response
        return response()->json([
            'sales_by_month' => $salesByMonth,
            'sales_by_day' => $salesByDay
        ], 200);
    }

    /**
     * Get the total revenue from all orders.
     * Sums up the total price of all orders.
     */
    public function totalRevenue()
    {
        // Get total revenue by day, grouping by the order date
        $totalRevenue = Order::selectRaw('DATE(created_at) as date, SUM(total_price) as revenue')
                            ->groupBy('date')
                            ->orderBy('date', 'asc')
                            ->get();

        // Return total revenue as JSON response
        return response()->json(['total_revenue' => $totalRevenue], 200);
    }

    /**
     * Get total sales for each product.
     * Returns the quantity sold and total sales for each product.
     */
    public function totalSalesByProduct()
    {
        // Retrieve products and calculate total quantity sold and total sales
        $products = Product::all();

        // Loop through each product and calculate sales data
        $productsData = $products->map(function ($product) {
            // Calculate total quantity sold and total sales for this product
            $totalQuantity = $product->orders()->sum('order_product.quantity');
            $totalSales = $product->orders()->sum(\Illuminate\Support\Facades\DB::raw('order_product.quantity * order_product.price'));

            return [
                'product_id' => $product->id,
                'name' => $product->name,
                'total_quantity' => $totalQuantity,
                'total_sales' => $totalSales
            ];
        });

        // Return the products with sales data as JSON response
        return response()->json($productsData, 200);
    }

    /**
     * Get orders grouped by their delivery status.
     * Returns the count of orders for each status (pending, shipped, delivered).
     */
    public function ordersByStatus()
    {
        // Group orders by delivery status and count them
        $ordersByStatus = Order::selectRaw('delivery_status, COUNT(*) as total_orders')
            ->groupBy('delivery_status')
            ->get();

        // Return the grouped orders by status as JSON response
        return response()->json($ordersByStatus, 200);
    }

    /**
     * Get the count of orders with 'shipped' status.
     * Returns the count of shipped orders.
     */
    public function shippedCount()
    {
        // Count the orders with 'shipped' status
        $shippedOrdersCount = Order::where('delivery_status', 'shipped')->count();

        // Return the count of shipped orders as JSON response
        return response()->json(['count' => $shippedOrdersCount]);
    }

    /**
     * Get the count of orders with 'pending' status.
     * Returns the count of pending orders.
     */
    public function pendingCount()
    {
        // Count the orders with 'pending' status
        $pendingOrdersCount = Order::where('delivery_status', 'pending')->count();

        // Return the count of pending orders as JSON response
        return response()->json(['count' => $pendingOrdersCount]);
    }

    /**
     * Show all orders with their associated products and customers.
     * Eager loads products and customers associated with each order.
     */
    public function index()
    {
        // Retrieve all orders with their associated products and customer
        return response()->json(Order::with('products', 'customer')->get());
    }

    /**
     * Create a new order.
     * Validates the request, stores the order, and associates products with it.
     */
    public function store(Request $request)
    {
        // Validate the incoming request to ensure required fields
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',  // Ensure the customer exists
            'order_date' => 'required|date',
            'delivery_status' => 'required|in:pending,shipped,delivered', // Validate delivery status
            'products' => 'required|array',  // Products should be an array
            'products.*.id' => 'required|exists:products,id',  // Ensure product exists
            'products.*.quantity' => 'required|integer|min:1',  // Ensure quantity is valid
        ]);

        // Create a new order with validated data
        $order = Order::create([
            'customer_id' => $validated['customer_id'],
            'order_date' => $validated['order_date'],
            'delivery_status' => $validated['delivery_status'],
        ]);

        // Attach products to the order (using the pivot table 'order_product')
        foreach ($validated['products'] as $product) {
            $order->products()->attach($product['id'], [
                'quantity' => $product['quantity'],
                'price' => \App\Models\Product::find($product['id'])->price,  // Get product price
            ]);
        }

        // Return the created order as a JSON response
        return response()->json($order, 201);
    }

    /**
     * Show a specific order along with its associated products.
     * Displays an individual order with related products.
     */
    public function show(Order $order)
    {
        // Load the associated products for the specific order and return it
        return response()->json($order->load('products'));
    }

    /**
     * Update an existing order.
     * Validates the request, updates the order and its products if necessary.
     */
    public function update(Request $request, Order $order)
    {
        // Validate the incoming request for order details and products
        $validated = $request->validate([
            'order_date' => 'nullable|date',
            'delivery_status' => 'nullable|in:pending,shipped,delivered', 
            'products' => 'nullable|array',
            'products.*.id' => 'nullable|exists:products,id',
            'products.*.quantity' => 'nullable|integer|min:1',
        ]);

        // Update the order with new data if provided
        $order->update([
            'order_date' => $validated['order_date'] ?? $order->order_date,
            'delivery_status' => $validated['delivery_status'] ?? $order->delivery_status,
        ]);

        // Update the products if provided in the request
        if (isset($validated['products'])) {
            foreach ($validated['products'] as $product) {
                $order->products()->updateExistingPivot($product['id'], [
                    'quantity' => $product['quantity'],
                    'price' => \App\Models\Product::find($product['id'])->price, // Get product price
                ]);
            }
        }

        // Return the updated order as a JSON response
        return response()->json($order);
    }

    /**
     * Delete a specific order.
     * Removes the order and any associated pivot table records.
     */
    public function destroy(Order $order)
    {
        // Delete the order, which also removes related records in the pivot table
        $order->delete();

        // Return a success message upon successful deletion
        return response()->json(['message' => 'Order deleted successfully']);
    }
}
