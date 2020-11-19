<?php

namespace samarnas;

use Illuminate\Database\Eloquent\Model;

class Refunddetails extends Model
{
   protected $fillable = ['refund_id','payment_id','booking_id','user_id','sub_category_id','paid_amount'];
}
 