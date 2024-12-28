<?php  

namespace App\Http\Controllers;  

use Illuminate\Http\Request;  
use App\Models\Store;  
use Illuminate\Database\Eloquent\ModelNotFoundException;  
use Illuminate\Validation\ValidationException;  
use Symfony\Component\HttpFoundation\Response;  

class StoreController extends Controller  
{  
    // Display a listing of the stores  
    public function index(Request $request)  
    {  
        try {  
            $searchTerm = $request->query('q');  
            $query = Store::query()->with('products'); // Start a query builder instance  

            // If there's a search term, filter the stores  
            if ($searchTerm) {  
                $query->where('name', 'like', "%$searchTerm%")  
                      ->orWhere('description', 'like', "%$searchTerm%");  
            }  

            $stores = $query->paginate(20); // Paginate the results  
            return response()->json($stores, 200);  
        } catch (\Exception $e) {  
            return response()->json(['error' => 'Failed to retrieve stores.'], Response::HTTP_INTERNAL_SERVER_ERROR);  
        }  
    }  

    // Create a new store  
    public function store(Request $request)  
    {  
        try {  
            $request->validate([  
                'name' => 'required|string|max:255',  
                'description' => 'nullable|string',  
                'location' => 'nullable|string|max:255',  
                'phone' => 'required|string|max:15',  
                'user_id' => 'required|exists:users,id' // Validate user_id presence  
            ]);  

            // Create a store based on the request data  
            $store = Store::create($request->all());  
            return response()->json($store, 201);  
        } catch (ValidationException $e) {  
            return response()->json(['error' => $e->validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);  
        } catch (\Exception $e) {  
            return response()->json(['error' => 'Failed to create store.'], Response::HTTP_INTERNAL_SERVER_ERROR);  
        }  
    }  

    // Show a specific store  
    public function show(Store $store)  
    {  
        return response()->json($store->load('products'), 200);  
    }  

    // Update a specific store  
    public function update(Request $request, Store $store)  
    {  
        try {  
            $request->validate([  
                'name' => 'sometimes|required|string|max:255',  
                'description' => 'nullable|string',  
                'location' => 'nullable|string|max:255',  
                'phone' => 'sometimes|required|string|max:15',  
                'user_id' => 'sometimes|required|exists:users,id' // Validate user_id presence when updating  
            ]);  

            // Update the store with the user's request data  
            $store->update($request->all());  
            return response()->json($store, 200);  
        } catch (ValidationException $e) {  
            return response()->json(['error' => $e->validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);  
        } catch (ModelNotFoundException $e) {  
            return response()->json(['error' => 'Store not found.'], Response::HTTP_NOT_FOUND);  
        } catch (\Exception $e) {  
            return response()->json(['error' => 'Failed to update store.'], Response::HTTP_INTERNAL_SERVER_ERROR);  
        }  
    }  

    // Delete a specific store  
    public function destroy(Store $store)  
    {  
        try {  
            $store->delete();  
            return response()->json(null, 204);  
        } catch (\Exception $e) {  
            return response()->json(['error' => 'Failed to delete store.'], Response::HTTP_INTERNAL_SERVER_ERROR);  
        }  
    }  
}