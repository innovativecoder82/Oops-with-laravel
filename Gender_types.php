<?php

namespace samarnas;

use Illuminate\Database\Eloquent\Model;

class Gender_types extends Model
{
    protected $fillable = ['type_id','gender_type_name','gender_type_name_arab','gender_type_image']; 
}
