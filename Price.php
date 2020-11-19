<?php

namespace samarnas;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Price extends Model
{
    protected $fillable = [ "price_id","country_id","service_id","amount" ];
}
