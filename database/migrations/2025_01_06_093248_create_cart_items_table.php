<?php  

use App\Models\User;  
use App\Models\Product;  
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
        Schema::create('cart_items', function (Blueprint $table) {  
            $table->id();  
            $table->foreignIdFor(User::class); // Reference to the user  
            $table->foreignIdFor(Product::class); // Reference to the product  
            $table->integer('quantity')->default(1); // Quantity of the product  
            $table->timestamps();  
        });  
    }  

    /**  
     * Reverse the migrations.  
     */  
    public function down(): void  
    {  
        Schema::dropIfExists('cart_items');  
    }  
};