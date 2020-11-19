<?php



namespace samarnas;



use Illuminate\Database\Eloquent\Model;



class Addtocarts extends Model

{

    protected $fillable = ['sub_category_id','sub_category_name','sub_category_amount','user_id','sub_category_image','booked_time','gender_id','service_id','special_note']; 



}

