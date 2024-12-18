<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;

class StoreController extends Controller
{

    // دالة لعرض جميع المتاجر
    public function index()
    {
        // جلب جميع المتاجر مع المنتجات المرتبطة
        $stores = Store::with('products')->get();

        // التحقق إذا كان هناك متاجر
        if ($stores->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No stores found',
                'data' => null
            ], 404);
        }

        // إرجاع البيانات بصيغة JSON مع هيكل منظم
        return response()->json([
            'status' => 'success',
            'message' => 'Stores retrieved successfully',
            'data' => $stores
        ], 200);
    }

  // دالة البحث عن المتاجر
public function search(Request $request)
{
    // الحصول على القيمة التي سيتم البحث عنها
    $searchTerm = $request->query('q');

    // البحث في الاسم والوصف
    $stores = Store::where('name', 'like', "%$searchTerm%")
                   ->orWhere('description', 'like', "%$searchTerm%")
                   ->get();

    // التحقق إذا لم يتم العثور على أي متاجر
    if ($stores->isEmpty()) {
        return response()->json([
            'status' => 'error',
            'message' => 'No stores found matching your search query',
            'data' => null
        ], 404);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Stores retrieved successfully',
        'data' => $stores
    ], 200);
}


}
