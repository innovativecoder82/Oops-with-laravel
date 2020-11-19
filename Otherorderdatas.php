<?php

namespace samarnas;

use Illuminate\Database\Eloquent\Model;

class Otherorderdatas extends Model
{
    protected $fillable = ['user_id','sub_category_id','sub_category_name','sub_category_amount','cart_data','address','address_id','latitude','longitude','booking_id','booked_time','special_note','created_at']; 

}
