<?php

namespace samarnas;

use Illuminate\Database\Eloquent\Model;

class Employees extends Model
{
    protected $fillable = ['employee_id','employee_name','business_user_id']; 

}
 