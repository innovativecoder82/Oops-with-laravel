<?php

namespace samarnas;

use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    protected $fillable = ['id','provider_id','latitude','longitude','booking_id','sub_category_id','distance'];
}
