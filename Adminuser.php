<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;

class Adminuser extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        // 'password', 'remember_token',
        'remember_token',
    ];

    public function getProvider($id)
    {
        $provider    =   \DB::table('extras')
                        ->join('users', 'users.id','=','extras.provider_id')
                        ->select('extras.*','users.id')
                        ->where('extras.provider_id','=',$id)
                        ->get()->toArray();
                        $provider = json_decode(json_encode($provider),true);
                        //print_r($provider);
                        return $provider;
    }
}
