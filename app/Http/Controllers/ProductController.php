<?php  

namespace App\Http\Controllers;  

use Illuminate\Http\Request;  
use App\Models\Product;  
use Illuminate\Database\Eloquent\ModelNotFoundException;  
use Illuminate\Validation\ValidationException;  
use Symfony\Component\HttpFoundation\Response;  

class ProductController extends Controller  
{  
    // Display products for a specific store (paginated)  
    public function index(Request $request)  
    {  
        try {  
            $searchTerm = $request->query('q');  
            $query = Product::query(); // Start a query builder instance  

            // If there's a search term, filter the products  
            if ($searchTerm) {  
                $query->where('name', 'like', "%$searchTerm%")  
                      ->orWhere('description', 'like', "%$searchTerm%");  
            }  

            $products = $query->paginate(20); // Paginate the results  
            return response()->json($products, 200);  
        } catch (\Exception $e) {  
            return response()->json(['error' => 'Failed to retrieve products.'], Response::HTTP_INTERNAL_SERVER_ERROR);  
        }  
    }  


    // Create a new product  
    public function store(Request $request)  
    {  
        try {  
            $request->validate([  
                'store_id' => 'required|exists:stores,id',  
                'name' => 'required|string|max:255',  
                'description' => 'nullable|string',  
                'price' => 'required|numeric|min:0',  
                'quantity' => 'required|integer|min:0',  
                'image' => 'nullable|string',  
            ]);  

            $product = Product::create($request->all());  
            return response()->json($product, 201);  
        } catch (ValidationException $e) {  
            return response()->json(['error' => $e->validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);  
        } catch (\Exception $e) {  
            return response()->json(['error' => 'Failed to create product.'], Response::HTTP_INTERNAL_SERVER_ERROR);  
        }  
    }  

    // Show a specific product  
    public function show(Product $product)  
    {  
        return response()->json($product, 200);  
    }  

    // Update a specific product  
    public function update(Request $request, Product $product)  
    {  
        try {  
            $request->validate([  
                'store_id' => 'sometimes|required|exists:stores,id',  
                'name' => 'sometimes|required|string|max:255',  
                'description' => 'nullable|string',  
                'price' => 'sometimes|required|numeric|min:0',  
                'quantity' => 'sometimes|required|integer|min:0',  
                'image' => 'nullable|string',  
            ]);  

            $product->update($request->all());  
            return response()->json($product, 200);  
        } catch (ValidationException $e) {  
            return response()->json(['error' => $e->validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);  
        } catch (ModelNotFoundException $e) {  
            return response()->json(['error' => 'Product not found.'], Response::HTTP_NOT_FOUND);  
        } catch (\Exception $e) {  
            return response()->json(['error' => 'Failed to update product.'], Response::HTTP_INTERNAL_SERVER_ERROR);  
        }  
    }  

    // Delete a specific product  
    public function destroy(Product $product)  
    {  
        try {  
            $product->delete();  
            return response()->json(null, 204);  
        } catch (\Exception $e) {  
            return response()->json(['error' => 'Failed to delete product.'], Response::HTTP_INTERNAL_SERVER_ERROR);  
        }  
    }  
}