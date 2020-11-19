<?php

namespace samarnas;

use Illuminate\Database\Eloquent\Model;
use Eloquent;
use DB;

class ProviderSubcategory extends Model 
{
    protected $table = 'provider_subcategory';
    
    protected $fillable = ['id','sub_category_id','provider_id']; 
}