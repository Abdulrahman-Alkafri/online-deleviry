<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer'
        ]);

        $user = auth()->user(); // افترض أن المستخدم مسجل دخول
        $product = Product::find($request->product_id);

        // التحقق إذا كانت الكمية سالبة للإزالة
        if ($request->quantity <= 0) {
            // البحث عن المنتج في السلة
            $cartItem = Cart::where('user_id', $user->id)
                            ->where('product_id', $request->product_id)
                            ->first();

            if (!$cartItem) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found in the cart',
                ], 404);
            }

            // إزالة المنتج من السلة
            $cartItem->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Product removed from cart successfully',
            ], 200);
        }

        // التحقق من الكمية المتوفرة إذا كانت إضافة
        if ($product->quantity < $request->quantity) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient stock for the requested quantity',
            ], 400);
        }

        // إضافة المنتج أو تحديث الكمية إذا كان موجودًا بالفعل
        $cartItem = Cart::where('user_id', $user->id)
                        ->where('product_id', $request->product_id)
                        ->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            Cart::create([
                'user_id' => $user->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Product added to cart successfully',
        ], 200);
    }

}

