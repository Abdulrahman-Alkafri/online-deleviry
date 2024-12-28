<?php  

namespace App\Models;  

use Illuminate\Database\Eloquent\Factories\HasFactory;  
use Illuminate\Database\Eloquent\Model;  

class Store extends Model  
{  
    use HasFactory;  

    protected $fillable = ['name', 'description', 'location', 'phone', 'user_id']; // Include user_id  

    // العلاقة مع المنتجات  
    public function products()  
    {  
        return $this->hasMany(Product::class);  
    }  

    // العلاقة مع المستخدم  
    public function user()  
    {  
        return $this->belongsTo(User::class);  
    }  
}