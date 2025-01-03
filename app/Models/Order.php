<?php  

namespace App\Models;  

use Illuminate\Database\Eloquent\Factories\HasFactory;  
use Illuminate\Database\Eloquent\Model;  

class Order extends Model  
{  
    use HasFactory;  

    protected $fillable = ['user_id', 'product_id', 'quantity', 'status'];  

    // Relationship with User  
    public function user()  
    {  
        return $this->belongsTo(User::class);  
    }  

  // In Order model  
public function products()  
{  
    return $this->belongsToMany(Product::class)  
                ->withPivot('quantity')  
                ->withTimestamps();  
}
}