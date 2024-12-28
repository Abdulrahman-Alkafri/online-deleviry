<?php  

namespace App\Models;  

use Illuminate\Database\Eloquent\Factories\HasFactory;  
use Illuminate\Foundation\Auth\User as Authenticatable;  
use Illuminate\Notifications\Notifiable;  
use Laravel\Sanctum\HasApiTokens;  

class User extends Authenticatable  
{  
    use HasFactory, Notifiable, HasApiTokens;  

    protected $fillable = [  
        'name',  
        'phone', // Add phone to fillable  
        'password',
        'role', 
        'verification_code', // Add verification code  
        'is_verified', // Add verification status  
        'image', // Add user image 
        'reset_code',
        'location' // Add location to fillable  
    ];  

    protected $hidden = [  
        'password',  
        'remember_token',  
        'verification_code', // Hide verification code  
    ];
    protected function casts(): array  
    {  
        return [  
            'email_verified_at' => 'datetime',  
            'password' => 'hashed',  
        ];  
    }  
    public function favorites()  
    {  
        return $this->belongsToMany(Product::class, 'favorites')->withTimestamps();  
    } 
}