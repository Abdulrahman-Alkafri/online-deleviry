<?php

use App\Models\Store;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Store::class); // الربط مع المتجر
            $table->string('name'); // اسم المنتج
            $table->text('description')->nullable(); // وصف المنتج
            $table->decimal('price', 10, 2); // السعر
            $table->string('image')->nullable(); // لتخزين مسار الصورة
            $table->integer('quantity')->default(0); // الكمية المتوفرة
            $table->timestamps();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};