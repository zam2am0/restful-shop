<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Models\Product;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() :JsonResponse
    {
        //
        $products = Product::all(); //get all data from database

        if ($products->isEmpty()) { //response if productslist is empty -> return 404
            return response()->json([
                'success' => false,
                'description' => "No products found",
                'data' => null
            ], 404); 
        }

        return response()->json([  //response if product is found ->return 200 ok
            'success' => true,
            'description' => "Products retrieved successfully",
            'data' => $products
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request):JsonResponse
    {
        //
        $validatedData = $request->validate([
            'Name' => 'required|string|max:255',
            'Description' => 'required|string',
            'Price' => 'required|numeric|min:0',
            'StockQuantity' => 'required|integer|min:0',
        ]);


        try {
            $product = Product::create($validatedData);  // Create the product using the validated data.

            return response()->json([
                'success' => true,
                'description' => "Product created successfully",
                'data' => $product
            ], 201);
    
        } catch (\Exception $e) {
            // Catch any errors and return a 500 error.
            return response()->json([
                'success' => false,
                'description' => "Product creation failed",
                'error' => $e->getMessage()
            ], 500);
        }       
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) : JsonResponse
    {
        //
        $product = Product::find($id); // Find product by its ID.

        if (!$product) {
            return response()->json([
                'success' => false,
                'description' => "Product not found",
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'description' => "Product retrieved successfully",
            'data' => $product
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $product = Product::find($id); // Find the product by ID.

    if (!$product) {
        return response()->json([ // If the product does not exist, return a 404 error.
            'success' => false,
            'description' => "Product not found",
            'data' => null
        ], 404);
    }

    // Validate data.
    $validatedData = $request->validate([
        'Name' => 'sometimes|string|max:255',
        'Price' => 'sometimes|numeric',
        'Description' => 'nullable|string',
        'StockQuantity' => 'sometimes|integer',
    ]);

    try {
        $product->update($validatedData); // Update the product with the validated data.

        // Return success response with updated product data.
        return response()->json([
            'success' => true,
            'description' => "Product updated successfully",
            'data' => $product
        ], 200);

    } catch (\Exception $e) {
        // Catch any exceptions and return a 500 error response.
        return response()->json([
            'success' => false,
            'description' => "Product update failed",
            'error' => $e->getMessage()
        ], 500);
    }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $product = Product::find($id); // Find the product by ID.

        if (!$product) {
            return response()->json([
                'success' => false,
                'description' => "Product not found",
                'data' => null
            ], 404);
        }

        try {
            $product->delete(); // Delete the product.
            return response()->json([
                'success' => true,
                'description' => "Product deleted successfully"
            ], 200);

        } catch (\Exception $e) {
            // Catch any errors during deletion and return a 500 error.
            return response()->json([
                'success' => false,
                'description' => "Product deletion failed",
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
