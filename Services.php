<?php

namespace samarnas;

use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    protected $fillable = ['service_id','service_name','service_name_arab','gender_id','image'];
}
