<?php  

namespace App\Http\Controllers;  

use App\Models\Order;  
use App\Models\Product;  
use Illuminate\Http\Request;  
use Illuminate\Support\Facades\Auth;  
use Illuminate\Validation\ValidationException;  
use Symfony\Component\HttpFoundation\Response;  

class OrderController extends Controller  
{  
    // Place a new order  
    public function placeOrder(Request $request)  
    {  
        try {  
            $request->validate([  
                'product_id' => 'required|exists:products,id',  
                'quantity' => 'required|integer|min:1',  
            ]);  

            $product = Product::findOrFail($request->product_id);  

            if ($product->quantity < $request->quantity) {  
                return response()->json(['error' => 'Insufficient product quantity.'], Response::HTTP_BAD_REQUEST);  
            }  

            // Create the order  
            $order = Order::create([  
                'user_id' => Auth::id(),  
                'product_id' => $product->id,  
                'quantity' => $request->quantity,  
                'status' => 'pending',  
            ]);  

            // Decrease the product quantity  
            $product->decrement('quantity', $request->quantity);  

            return response()->json($order, Response::HTTP_CREATED);  
        } catch (ValidationException $e) {  
            return response()->json(['error' => $e->validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);  
        } catch (\Exception $e) {  
            return response()->json(['error' => 'Failed to place order.'], Response::HTTP_INTERNAL_SERVER_ERROR);  
        }  
    }  

    // View all orders for the authenticated user  
    public function viewOrders()  
    {  
        $orders = Order::where('user_id', Auth::id())->with('product')->get();  
        return response()->json($orders, Response::HTTP_OK);  
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

            return response()->json($order, Response::HTTP_OK);  
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
            if ($order->status !== 'delivered') {  
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
            if (!$order->user_id->role === "admin") {  
                return response()->json(['error' => 'Unauthorized.'], Response::HTTP_FORBIDDEN);  
            }  

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
}