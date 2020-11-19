<?php

namespace samarnas;

use Illuminate\Database\Eloquent\Model;

class Extras extends Model
{
   protected $fillable = ['provider_id','address','city','state','zip_code','experience_doc','latitude','longitude','bussiness_license','language','country_name','country_id','document_status'];
}
 