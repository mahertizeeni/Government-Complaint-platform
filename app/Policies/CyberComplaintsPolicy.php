<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Employee;
use App\Models\CyberComplaint;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable;

class CyberComplaintsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Authenticatable  $user): bool
    {
        return $user instanceof Employee|| $user instanceof User ;
    }
    

    /**
     * Determine whether the user can view the model.
     */
    public function view(Authenticatable  $user, CyberComplaint $cyberComplaint): bool
    {
      if($user instanceof Employee)
      {
        return $user->government_entity_id===12&&$user->city_id===15;
      }
      elseif($user instanceof User)
      { 
        return $user->id === $cyberComplaint->user_id;
      }
      return false ;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Authenticatable  $user): bool
    {
        return $user instanceof User ;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Authenticatable $user, CyberComplaint $cyberComplaint): bool
    {
        return $user instanceof Employee&&
        $user->government_entity_id===12 &&
        $user->city_id===15 ;
        
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Authenticatable $user, CyberComplaint $cyberComplaint): bool
    {
        return $user instanceof User && $user->id === $cyberComplaint->user_id;

    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Authenticatable $user, CyberComplaint $cyberComplaint): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Authenticatable $user, CyberComplaint $cyberComplaint): bool
    {
        return false;
    }
}
