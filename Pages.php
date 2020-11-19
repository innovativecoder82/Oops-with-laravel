<?php

namespace samarnas;

use Illuminate\Database\Eloquent\Model;

class Pages extends Model
{
    protected $fillable = ['page_id','title','content']; 
    
}
