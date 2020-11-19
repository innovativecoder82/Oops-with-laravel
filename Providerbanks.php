<?php

namespace samarnas;

use Illuminate\Database\Eloquent\Model;

class Providerbanks extends Model
{
   protected $fillable = ['account_name','provider_id','account_number','currency','country','routing_number','stripe_id']; 

}
