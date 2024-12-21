<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;  


Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-phone', [AuthController::class, 'verifyPhone']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/request-reset-code', [AuthController::class, 'requestResetCode']);
Route::post('/reset-password-with-code', [AuthController::class, 'resetPasswordWithCode']);
Route::get("/users",[AuthController::class,"index"])->middleware("auth:sanctum");
Route::get("/user/{user}",[AuthController::class,"show"])->middleware("auth:sanctum");
Route::put("/user/{user}",[AuthController::class,"update"])->middleware("auth:sanctum");
Route::delete("/user/{user}",[AuthController::class,"destroy"])->middleware("auth:sanctum");





//------------------API Product-------------------//  
Route::get('/products', [ProductController::class, 'index']);

// Protected routes for creating, updating, and deleting products  
Route::middleware(['auth:sanctum'])->group(function () {  
    Route::post('/products', [ProductController::class, 'store'])->middleware('can:create,App\Models\Product'); // Only admins can create  
    Route::get('/products/{product}', [ProductController::class, 'show']); // Show a specific product  
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('can:delete,product'); // Only admins can delete  
});  

//------------------- API Stores -------------------//  
Route::get('/stores', [StoreController::class, 'index']); // Fetch stores  
Route::get('/stores/search', [StoreController::class, 'search']); // Search for stores  

// Protected routes for creating, updating, and deleting stores  
Route::middleware(['auth:sanctum'])->group(function () {  
    Route::post('/stores', [StoreController::class, 'store'])->middleware('can:create,App\Models\Store'); // Only admins can create  
    Route::get('/stores/{store}', [StoreController::class, 'show']); // Show a specific store  
    Route::put('/stores/{store}', [StoreController::class, 'update']);
    Route::delete('/stores/{store}', [StoreController::class, 'destroy'])->middleware('can:delete,store'); // Only admins can delete  
});

// ------------------- API orders ------------------//

// Routes for Order Management  
Route::middleware('auth:sanctum')->group(function () {  
    Route::post('/orders', [OrderController::class, 'placeOrder']); // Place an order  
    Route::get('/orders', [OrderController::class, 'viewOrders']); // View user orders  
    Route::patch('/orders/{order}', [OrderController::class, 'updateOrder']); // Update order  
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']); // Delete order  

    // Admin route to change order status  
    Route::patch('/orders/{order}/status', [OrderController::class, 'changeOrderStatus'])->middleware('can:isAdmin');  
});