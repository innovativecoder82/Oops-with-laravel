<?php



namespace samarnas;



use Illuminate\Database\Eloquent\Model;



class Bookings extends Model

{

    protected $fillable = ['user_id','booking_status','address_id','address','latitude','longitude']; 



}

