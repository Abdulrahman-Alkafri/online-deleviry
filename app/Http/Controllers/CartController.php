<?php  

namespace App\Http\Controllers;  

use App\Models\CartItem;  
use Illuminate\Http\Request;  
use Illuminate\Support\Facades\Auth;  
use Illuminate\Support\Facades\DB;

class CartController extends Controller  
{  
    // Add item to cart  
    public function addToCart(Request $request)  
    {  
        $request->validate([  
            'product_id' => 'required|exists:products,id',  
            'quantity' => 'required|integer|min:1',  
        ]);  

        // Attempt to update or create the cart item  
        $cartItem = CartItem::updateOrCreate(  
            [  
                'user_id' => Auth::id(),  
                'product_id' => $request->product_id,  
            ],  
            ['quantity' => DB::raw('quantity + ' . $request->quantity)]  
        );  

        // Fetch the updated cart item after incrementing the quantity  
        $cartItem = CartItem::where('id', $cartItem->id)->with('product')->first();  

        return response()->json(['message' => 'Product added to cart successfully!', 'cart_item' => $cartItem]);  
    }
    // Remove item from cart  
    public function removeFromCart($id)  
    {  
        $cartItem = CartItem::where('id', $id)->where('user_id', Auth::id())->first();  

        if ($cartItem) {  
            $cartItem->delete();  
            return response()->json(['message' => 'Product removed from cart successfully!']);  
        }
        return response()->json(['message' => 'Cart item not found!'], 404);  
    }  

    // Update item quantity in cart  
    public function updateCart(Request $request, $id)  
    {  
        $request->validate([  
            'quantity' => 'required|integer|min:1',  
        ]);  

        $cartItem = CartItem::where('id', $id)->where('user_id', Auth::id())->first();  

        if ($cartItem) {  
            $cartItem->update(['quantity' => $request->quantity]);  
            return response()->json(['message' => 'Cart item updated successfully!', 'cart_item' => $cartItem]);  
        }  

        return response()->json(['message' => 'Cart item not found!'], 404);  
    }  

    // Reset the cart  
    public function resetCart()  
    {  
        CartItem::where('user_id', Auth::id())->delete();  
        return response()->json(['message' => 'Cart has been reset successfully!']);  
    }  

    // Get total bill  
    public function getTotal()  
{  
    // Calculate the total using a raw expression  
    $total = CartItem::where('user_id', Auth::id())  
        ->join('products', 'cart_items.product_id', '=', 'products.id')  
        ->selectRaw('SUM(cart_items.quantity * products.price) as total') // Change is here  
        ->value('total'); // Retrieve only the total value  

    return response()->json(['total' => $total ? $total : 0]); // Handle case where total may be null  
}
    // Get cart items  
    public function getCartItems()  
    {  
        $cartItems = CartItem::where('user_id', Auth::id())  
            ->with('product')  
            ->get();  

        return response()->json($cartItems);  
    }  
}