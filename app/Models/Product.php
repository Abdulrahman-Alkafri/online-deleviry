<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['store_id', 'name', 'description', 'price', 'quantity','image'];

    // العلاقة مع المتجر
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
    // In Product model  
    public function orders()  
    {  
        return $this->belongsToMany(Order::class)  
                    ->withPivot('quantity')  
                    ->withTimestamps();  
    }
    public function favoritedBy()  
    {  
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();  
    } 
}