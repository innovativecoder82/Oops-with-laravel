<?php

namespace samarnas;

use Illuminate\Database\Eloquent\Model;

class Carddetail extends Model
{
   protected $fillable = ['card_number','exp_year','exp_month','cvv','country','card_type','user_id']; 

}
