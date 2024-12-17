<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;



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
// تصفح منتجات متجر معين
Route::get('/getProductsInStores/{store}', [ProductController::class, 'getProductsInStores']);
// البحث عن المنتجات
Route::get('/products/search', [ProductController::class, 'search']);
// عرض تفاصيل المنتج
Route::get('/products/{product}', [ProductController::class, 'show']);



            //------------------- API Stores -------------------//

// تصفح المتاجر
Route::get('/stores', [StoreController::class, 'index']);
// البحث عن المتاجر
Route::get('/stores/search', [StoreController::class, 'search']);

