<?php  

namespace App\Http\Controllers;  

use App\Models\Favorite;  
use App\Models\Product;  
use Illuminate\Http\Request;  
use Illuminate\Support\Facades\Auth;  

class FavoriteController extends Controller  
{  
    public function store($productId)  
    {  
        $user = Auth::user();  
        $product = Product::findOrFail($productId);  

        // Create a new favorite entry  
        Favorite::create([  
            'user_id' => $user->id,  
            'product_id' => $product->id,  
        ]);  

        return response()->json(['message' => 'Product added to favorites'], 200);  
    }  

    public function destroy($productId)  
    {  
        $user = Auth::user();  

        // Delete the favorite entry if it exists  
        Favorite::where('user_id', $user->id)  
            ->where('product_id', $productId)  
            ->delete();  

        return response()->json(['message' => 'Product removed from favorites'], 200);  
    }  

    public function index()  
    {  
        $user = Auth::user();  

        // Retrieve the user's favorite products  
        $favorites = Favorite::with('product')->where('user_id', $user->id)->get();  

        return response()->json($favorites);  
    }  
}