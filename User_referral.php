<?php

namespace samarnas;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class User_referral extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'referral_id','referral_code','user_id','created_at','updated_at','first_order_flag'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
   
    
}
