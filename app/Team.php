<?php

namespace App;

use Illuminate\Notifications\Notifiable;

class Team extends BaseModel
{
    use Notifiable;

    public function members(){
        return $this->belongsToMany(User::class, "memberships");
    }

    public function memberships(){
        return $this->hasMany(Membership::class);
    }

    public function tickets(){
        return $this->hasMany(Ticket::class);
    }
}
