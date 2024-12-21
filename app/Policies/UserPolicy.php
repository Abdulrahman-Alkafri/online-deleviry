<?php  

namespace App\Policies;  

use App\Models\User;  
use Illuminate\Auth\Access\HandlesAuthorization;  

class UserPolicy  
{  
    use HandlesAuthorization;  

    /**  
     * Determine whether the user is a super admin.  
     *  
     * @param \App\Models\User $user  
     * @return bool  
     */  
    public function isSuperAdmin(User $user)  
    {  
        return $user->role === 'super admin';  
    }  
}