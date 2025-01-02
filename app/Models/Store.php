<?php  

namespace App\Models;  

use Illuminate\Database\Eloquent\Factories\HasFactory;  
use Illuminate\Database\Eloquent\Model;  

class Store extends Model  
{  
    use HasFactory;  

    protected $fillable = ['name', 'description', 'location', 'phone', 'user_id', 'image']; // Include image  

    // Relationship with products  
    public function products()  
    {  
        return $this->hasMany(Product::class);  
    }  

    // Relationship with user  
    public function user()  
    {  
        return $this->belongsTo(User::class);  
    }  
}