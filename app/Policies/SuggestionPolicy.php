<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;
use App\Models\Employee;
use App\Models\Suggestion;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable;

class SuggestionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
     public function viewAny(Authenticatable $user): bool
    {
        return $user instanceof Employee || $user instanceof User || $user instanceof Admin ;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Suggestion $suggestion): bool
    {
        
        if($user instanceof Employee){
              
            return $user->city_id=== $suggestion->city_id 
            && $user->government_entity_id===$suggestion->government_entity_id ;
        }
        if($user instanceof User){
            return $user->id=== $suggestion->user_id ;
           
        }
        if($user instanceof Admin){
            return $suggestion->get() ;
           
        }
        return false ;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
         return $user instanceof User;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Suggestion $suggestion): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Suggestion $suggestion): bool
    {
        return $user instanceof User && $user->id === $suggestion->user_id || $user instanceof Admin ;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Suggestion $suggestion): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Suggestion $suggestion): bool
    {
        return false;
    }
}
