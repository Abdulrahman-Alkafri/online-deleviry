<?php  

namespace App\Policies;  

use App\Models\User;  
use App\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;  

class OrderPolicy  
{  
    use HandlesAuthorization;  

    /**  
     * Determine whether the user can change the order status.  
     *  
     * @param User $user  
     * @return bool  
     */  
    public function changeOrderStatus(User $user)  
    {  
        return $user->role === 'admin'; // Adjust this logic based on your requirements  
    }  
}