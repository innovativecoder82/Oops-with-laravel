<?php

namespace samarnas;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Country extends Model
{
    protected $fillable = [ "country_id","country_name","country_name_arab","country_code","currency_icon" ];
}
