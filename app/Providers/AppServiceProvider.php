<?php
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Policies\ProductPolicy;
use App\Policies\StorePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
protected $policies = [
User::class => UserPolicy::class,  
Store::class => StorePolicy::class,
Product::class => ProductPolicy::class,
];

public function boot(): void
{
$this->registerPolicies(); // <-- Ensure policies are registered
 
} 
}