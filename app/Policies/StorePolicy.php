<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Store;

    class StorePolicy
    {
        public function create(User $user)
        {
            return $user->role === 'admin';
        }

      

        public function delete(User $user, Store $store)
        {
            return $user->role === 'admin';
        }
    }