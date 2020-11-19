<?php

namespace samarnas;

use Illuminate\Database\Eloquent\Model;

class Services_processes extends Model
{
    protected $fillable = ['provider_id','start_latitude','start_longitude','end_latitude','end_longitude','user_id','booking_id','sub_category_id'];
}
