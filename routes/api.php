<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;  
use App\Models\Order;

//------------------API Auth-------------------// 
Route::post('/login', [AuthController::class, 'login']);  
Route::post('/register', [AuthController::class, 'register']);  
Route::post('/verify-phone', [AuthController::class, 'verifyPhone']);  
Route::post('/request-reset-code', [AuthController::class, 'requestResetCode']);  
Route::post('/reset-password-with-code', [AuthController::class, 'resetPasswordWithCode']);  
Route::middleware('auth:sanctum')->group(function () {  
    Route::post('/logout', [AuthController::class, 'logout']);  
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);  
    Route::get('/users', [AuthController::class, 'index']);  
    Route::get('/user/{user}', [AuthController::class, 'show']);  
    Route::put('/user/{user}', [AuthController::class, 'update']);  
    Route::delete('/user/{user}', [AuthController::class, 'destroy']);  
});
//------------------API Product-------------------//  
Route::get('/products', [ProductController::class, 'index']);
Route::middleware(['auth:sanctum'])->group(function () {  
    Route::post('/products', [ProductController::class, 'store'])->middleware('can:create,App\Models\Product'); // Only admins can create  
    Route::get('/products/{product}', [ProductController::class, 'show']); // Show a specific product  
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('can:delete,product'); // Only admins can delete  
});  

//------------------- API Stores -------------------//  
Route::get('/stores', [StoreController::class, 'index']); // Fetch stores  

Route::middleware(['auth:sanctum'])->group(function () {  
    Route::post('/stores', [StoreController::class, 'store'])->middleware('can:create,App\Models\Store'); // Only admins can create  
    Route::get('/stores/{store}', [StoreController::class, 'show']); // Show a specific store  
    Route::put('/stores/{store}', [StoreController::class, 'update']);
    Route::delete('/stores/{store}', [StoreController::class, 'destroy'])->middleware('can:delete,store'); // Only admins can delete 
    Route::get('/stores/{store}/orders', [OrderController::class, 'getOrdersForStore'])->middleware('can:delete,store');;  
 
});


// Routes for Order Management  
Route::middleware('auth:sanctum')->group(function () {  
    Route::post('/orders', [OrderController::class, 'placeOrder']); // Place an order  
    Route::get('/orders/{userId}', [OrderController::class, 'viewOrders']);  
    Route::patch('/orders/{order}', [OrderController::class, 'updateOrder']); // Update order  
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']); // Delete order  

    // Admin route to change order status  
    Route::patch('/orders/{order}/status', [OrderController::class, 'changeOrderStatus'])  
    ->can('changeOrderStatus', Order::class);  
});
// ---------------------- API Cart  -----------------//
use App\Http\Controllers\CartController;  

Route::middleware('auth:sanctum')->group(function () {  
    Route::post('/cart/add', [CartController::class, 'addToCart']);  
    Route::delete('/cart/remove/{id}', [CartController::class, 'removeFromCart']);  
    Route::put('/cart/update/{id}', [CartController::class, 'updateCart']);  
    Route::delete('/cart/reset', [CartController::class, 'resetCart']);  
    Route::get('/cart/total', [CartController::class, 'getTotal']);  
    Route::get('/cart/items', [CartController::class, 'getCartItems']);  
});

// ---------------------- API favorites -----------------//
use App\Http\Controllers\FavoriteController;  

Route::middleware('auth:sanctum')->group(function () {  
    Route::post('/favorites/{productId}', [FavoriteController::class, 'store']);  
    Route::delete('/favorites/{productId}', [FavoriteController::class, 'destroy']);  
    Route::get('/favorites', [FavoriteController::class, 'index']);  
});