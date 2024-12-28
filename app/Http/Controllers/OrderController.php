<?php  

namespace App\Http\Controllers;  

use App\Models\Order;  
use App\Models\Product;
use Illuminate\Http\Request;  
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;  
use Symfony\Component\HttpFoundation\Response;  
use Illuminate\Support\Facades\Gate;  

class OrderController extends Controller  
{  
    // Place a new order  
    public function placeOrder(Request $request)  
    {  
        try {  
            $request->validate([  
                'products' => 'required|array',  
                'products.*.product_id' => 'required|exists:products,id',  
                'products.*.quantity' => 'required|integer|min:1',  
            ]);  
    
            $orders = [];  
            foreach ($request->products as $productData) {  
                $product = Product::findOrFail($productData['product_id']);  
    
                if ($product->quantity < $productData['quantity']) {  
                    return response()->json(['error' => 'Insufficient product quantity for product ID ' . $product->id], Response::HTTP_BAD_REQUEST);  
                }  
    
                // Create the order  
                $order = Order::create([  
                    'user_id' => Auth::id(),  
                    'status' => 'pending',  
                ]);  
    
                // Attach the product to the order with quantity  
                $order->products()->attach($product->id, ['quantity' => $productData['quantity']]);  
    
                // Decrease the product quantity  
                $product->decrement('quantity', $productData['quantity']);  
    
                $orders[] = $order;  
            }  
    
            return response()->json($orders, Response::HTTP_CREATED);  
        } catch (ValidationException $e) {  
            return response()->json(['error' => $e->validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);  
        } catch (\Exception $e) {  
            return response()->json(['error' => 'Failed to place order.'], Response::HTTP_INTERNAL_SERVER_ERROR);  
        }  
    }

    // View all orders for the authenticated user  
   // View all orders for a specific user  
public function viewOrders($userId)  
{  
    try {  

        $orders = Order::where('user_id', $userId)->with('products')->get();  

        return response()->json($orders, Response::HTTP_OK);  
    } catch (\Exception $e) {  
        return response()->json(['error' => 'Failed to retrieve orders.'], Response::HTTP_INTERNAL_SERVER_ERROR);  
    }  
} 

    // Update an existing order (cancel or edit)  
    public function updateOrder(Request $request, Order $order)  
    {  
        try {  
            if ($order->user_id !== Auth::id()) {  
                return response()->json(['error' => 'Unauthorized.'], Response::HTTP_FORBIDDEN);  
            }  
    
            $request->validate([  
                'quantity' => 'sometimes|required|integer|min:1',  
                'status' => 'sometimes|required|in:pending,canceled',  
                'products' => 'sometimes|array', // New field for adding products  
                'products.*.id' => 'required|exists:products,id', // Validate product IDs  
                'products.*.quantity' => 'required|integer|min:1', // Validate product quantities  
            ]);  
    
            // Allow cancellation if the order is still pending  
            if ($request->status === 'canceled' && $order->status === 'pending') {  
                $order->update(['status' => 'canceled']);  
                // Increase the product quantity back  
                $product = $order->product;  
                $product->increment('quantity', $order->quantity);  
            } elseif ($request->has('quantity')) {  
                // Update quantity if the order is still pending  
                if ($order->status === 'pending') {  
                    // Adjust product quantity  
                    $product = $order->product;  
                    $product->increment('quantity', $order->quantity); // Return previous quantity  
                    $order->update(['quantity' => $request->quantity]);  
                    $product->decrement('quantity', $request->quantity); // Deduct new quantity  
                } else {  
                    return response()->json(['error' => 'Cannot update a delivered or canceled order.'], Response::HTTP_BAD_REQUEST);  
                }  
            }  
    
            // Handle adding new products to the order  
            if ($request->has('products')) {  
                foreach ($request->products as $productData) {  
                    $product = Product::find($productData['id']);  
                    
                    // Check if there's enough quantity  
                    if ($product->quantity < $productData['quantity']) {  
                        return response()->json(['error' => 'Insufficient product quantity for product ID ' . $productData['id']], Response::HTTP_BAD_REQUEST);  
                    }  
                    
                    // Add the product to the order  
                    $order->products()->attach($product->id, ['quantity' => $productData['quantity']]);  
                    
                    // Decrease the product quantity in the products table  
                    $product->decrement('quantity', $productData['quantity']);  
                }  
            }  
    
            return response()->json($order->load('products'), Response::HTTP_OK);  
        } catch (ValidationException $e) {  
            return response()->json(['error' => $e->validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);  
        } catch (\Exception $e) {  
            return response()->json(['error' => 'Failed to update order.'], Response::HTTP_INTERNAL_SERVER_ERROR);  
        }  
    }
    // Delete an order (only delivered orders can be deleted)  
    public function destroy(Order $order)  
    {  
        try {  
            if ($order->status === "pending" ) {  
                return response()->json(['error' => 'Only delivered orders can be deleted.'], Response::HTTP_BAD_REQUEST);  
            }  

            $order->delete();  
            return response()->json(null, Response::HTTP_NO_CONTENT);  
        } catch (\Exception $e) {  
            return response()->json(['error' => 'Failed to delete order.'], Response::HTTP_INTERNAL_SERVER_ERROR);  
        }  
    }  

    // Admin can change the status of orders  
    public function changeOrderStatus(Request $request, Order $order)  
    {  
        try {  
            // if (!$request->order()->can('changeOrderStatus', Order::class)) {  
            //     return response()->json(['message' => 'Unauthorized.'], 403);  
            // }  
    
            $request->validate([  
                'status' => 'required|in:pending,delivered,canceled',  
            ]);  
    
            // Only allow changing status for pending and delivered orders  
            if ($order->status === 'delivered' && $request->status === 'canceled') {  
                return response()->json(['error' => 'Cannot cancel a delivered order.'], Response::HTTP_BAD_REQUEST);  
            }  
    
            $order->update(['status' => $request->status]);  
            return response()->json($order, Response::HTTP_OK);  
        } catch (ValidationException $e) {  
            return response()->json(['error' => $e->validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);  
        } catch (\Exception $e) {  
            return response()->json(['error' => 'Failed to change order status.'], Response::HTTP_INTERNAL_SERVER_ERROR);  
        }  
    } 
    public function getOrdersForStore($storeId)  
    {  
        $orders = DB::table('orders')  
            ->join('order_product', 'orders.id', '=', 'order_product.order_id')  
            ->join('products', 'order_product.product_id', '=', 'products.id')  
            ->where('products.store_id', $storeId)  
            ->select('orders.*') // You can select specific fields if needed  
            ->distinct() // To avoid duplicate orders if they have multiple products  
            ->get();  

        return response()->json($orders);  
    }
}