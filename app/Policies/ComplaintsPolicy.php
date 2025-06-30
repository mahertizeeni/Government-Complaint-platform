<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Contracts\Auth\Authenticatable;

class ComplaintsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Authenticatable $user): bool
    {
        return $user instanceof Employee || $user instanceof User ;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Authenticatable $user, Complaint $complaint): bool
    {
        if($user instanceof Employee){
              
            return $user->city_id=== $complaint->city_id 
            && $user->government_entity_id===$complaint->government_entity_id ;
        }
        if($user instanceof User){
            return $user->id=== $complaint->user_id ;
           
        }
        return false ;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Authenticatable $user): bool
    {
       return $user instanceof User;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Authenticatable $user, Complaint $complaint): bool
    {
        return $user instanceof Employee
            && $user->city_id=== $complaint->city_id 
            && $user->government_entity_id===$complaint-> government_entity_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Authenticatable $user, Complaint $complaint): bool
    {
        return $user instanceof User && $user->id === $complaint->user_id;

    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Authenticatable $user, Complaint $complaint): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Authenticatable $user, Complaint $complaint): bool
    {
        return false;
    }
}
