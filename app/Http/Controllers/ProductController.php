<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function limitedProducts(){
        $products = Product::limit(8)->get();
        return response()->json($products, 200);
    }
    public function filteredProductsByPrice(){
        $products = Product::orderBy('price', 'asc')->get();
        return response()->json($products, 200);
    }
    public function filteredProductsByTime(){
        $products = Product::orderBy('created_at', 'desc')->get();
        return response()->json($products, 200);
    }
    // Display all products
    public function index()
    {
        return response()->json(Product::with('orders')->get(), 200);
    }

    // Create a new product
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $product = Product::create($validated);

        if ($request->hasFile('image')) {
            $product->image = $request->file('image')->store('products', 'public');
            $product->save();
        }

        return response()->json($product, 201);
    }

    // Show a specific product
    public function show(Product $product)
    {
        return response()->json($product, 200);
    }

    // Update an existing product
    public function update(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:0',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            ]);

            $product->update($validated);

            if ($request->hasFile('image')) {
                $this->updateProductImage($product, $request->file('image'));
            }

            return response()->json($product, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function updateProductImage(Product $product, $image)
    {
        if ($product->image && Storage::exists('public/' . $product->image)) {
            Storage::delete('public/' . $product->image);
        }

        $product->image = $image->store('products', 'public');
        $product->save();
    }

    // Delete a product
    public function destroy(Product $product)
    {
        if ($product->image && Storage::exists('public/' . $product->image)) {
            Storage::delete('public/' . $product->image);
        }

        $product->delete();
        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
