<?php

namespace App\Observers;

use App\Models\CityFunction;
use App\Models\User;
use App\Enums\UserRole; 
use App\Notifications\NewFunctionAdded;
use Illuminate\Support\Facades\Notification;

class CityFunctionObserver
{
    public function created(CityFunction $cityFunction): void
    {
        // AANGEPAST: Zoek nu naar Planners EN Admins
        $experts = User::whereIn('role', [UserRole::PLANNER, UserRole::ADMIN])->get(); 
    
        Notification::send($experts, new NewFunctionAdded($cityFunction));
    }
}