<?php

namespace samarnas;

use Illuminate\Database\Eloquent\Model;

class Orderdetails extends Model
{
    
   protected $fillable = ['id','payment_id','payment_date','payment_token','booking_id','totalamount','stripe_status','user_id'];
  
}
 