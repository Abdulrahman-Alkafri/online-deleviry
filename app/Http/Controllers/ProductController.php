<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Store;

class ProductController extends Controller
{

      // دالة لعرض المنتجات المرتبطة بالمتجر المحدد
      public function getProductsInStores(Store $store)
      {
          // جلب المنتجات المرتبطة بالمتجر المحدد
          $products = $store->products;

          // التحقق إذا كان المتجر لا يحتوي على منتجات
          if ($products->isEmpty()) {
              return response()->json([
                  'status' => 'error',
                  'message' => 'No products found for this store',
                  'data' => null
              ], 404);
          }

          return response()->json([
              'status' => 'success',
              'message' => 'Products retrieved successfully',
              'data' => $products
          ], 200);
      }

// دالة البحث عن المنتجات
public function search_product(Request $request)
{
    // الحصول على القيمة التي سيتم البحث عنها
    $searchTerm = $request->query('q');

    // البحث في الاسم والوصف
    $products = Product::where('name', 'like', "%$searchTerm%")
                       ->orWhere('description', 'like', "%$searchTerm%")
                       ->get();

    if ($products->isEmpty()) {
        return response()->json([
            'status' => 'error',
            'message' => 'No products found matching your search query',
            'data' => null
        ], 404);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Products retrieved successfully',
        'data' => $products
    ], 200);
}

           // دالة لعرض تفاصيل منتج معين
    public function show($id)
    {
        // العثور على المنتج باستخدام ID
        $product = Product::find($id);

        // التحقق إذا كان المنتج موجودًا
        if ($product) {
            return response()->json([
                'status' => 'success',
                'message' => 'Product retrieved successfully',
                'data' => $product
            ], 200);
        }

        // في حالة لم يتم العثور على المنتج
        return response()->json([
            'status' => 'error',
            'message' => 'Product not found',
            'data' => null
        ], 404);
    }

}
