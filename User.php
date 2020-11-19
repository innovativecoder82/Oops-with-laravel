<?php

namespace samarnas;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fname','lname','mobile','dob','gender', 'user_type', 'id_proof', 'profile_pic', 'activation_token','email', 'password','device_token','active','device_type','admin_approve_status','active_language','referral_code'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         'remember_token', 'activation_token'
    ];
    
}
