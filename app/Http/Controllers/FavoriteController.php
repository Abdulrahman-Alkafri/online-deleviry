<?php  

namespace App\Http\Controllers;  

use App\Models\Favorite;  
use App\Models\Product;  
use Illuminate\Support\Facades\Auth;  
use Illuminate\Database\Eloquent\ModelNotFoundException;  
use Illuminate\Http\Response;  

class FavoriteController extends Controller  
{  
    public function store($productId)  
    {  
        $user = Auth::user();  

        // Check if the user is authenticated  
        if (!$user) {  
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);  
        }  

        // Attempt to find the product by ID  
        try {  
            $product = Product::findOrFail($productId);  
        } catch (ModelNotFoundException $e) {  
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);  
        }  

        // Create a new favorite entry  
        try {  
            Favorite::create([  
                'user_id' => $user->id,  
                'product_id' => $product->id,  
            ]);  
            return response()->json(['message' => 'Product added to favorites'], Response::HTTP_OK);  
        } catch (\Exception $e) {  
            return response()->json(['message' => 'Error adding product to favorites'], Response::HTTP_INTERNAL_SERVER_ERROR);  
        }  
    }  

    public function destroy($productId)  
    {  
        $user = Auth::user();  

        // Check if the user is authenticated  
        if (!$user) {  
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);  
        }  

        // Attempt to delete the favorite entry if it exists  
        $deleted = Favorite::where('user_id', $user->id)  
            ->where('product_id', $productId)  
            ->delete();  

        if ($deleted) {  
            return response()->json(['message' => 'Product removed from favorites'], Response::HTTP_OK);  
        } else {  
            return response()->json(['message' => 'Product was not in favorites'], Response::HTTP_NOT_FOUND);  
        }  
    }  

    public function index()  
    {  
        $user = Auth::user();  

        // Check if the user is authenticated  
        if (!$user) {  
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);  
        }  

        // Retrieve the user's favorite products  
        try {  
            $favorites = Favorite::with('product')->where('user_id', $user->id)->get();  
            return response()->json($favorites);  
        } catch (\Exception $e) {  
            return response()->json(['message' => 'Error retrieving favorite products'], Response::HTTP_INTERNAL_SERVER_ERROR);  
        }  
    }  
}