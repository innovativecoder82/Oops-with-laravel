<?php

namespace samarnas;

use Illuminate\Database\Eloquent\Model;
use Eloquent;
use DB;

class ProviderDocument extends Model 
{
    protected $table = 'provider_document';
    
    protected $fillable = ['document_id','provider_id','document_url','document_type','document_status']; 
}