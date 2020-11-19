<?php

namespace samarnas;

use Illuminate\Database\Eloquent\Model;

class Locations extends Model
{
    protected $fillable = ['address_id','fulladdress','user_id','flatno','landmark','location_type','pincode','latitude','longitude']; 
    
}
