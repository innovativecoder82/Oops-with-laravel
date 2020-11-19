<?php
namespace samarnas;

use Illuminate\Database\Eloquent\Model;
use DB;

class Rating extends Model
{

    protected $fillable = ['provider_id','user_id','quality_of_work','booking_id','request_id','comment','professionalism','value_of_money']; 

    
}

?>