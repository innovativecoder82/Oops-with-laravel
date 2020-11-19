<?php

namespace samarnas;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\Admin as Authenticatable;


class gender_service extends Model
{
   protected $fillable = ["gender_service_id", "service_id", "gender_id"];
}
