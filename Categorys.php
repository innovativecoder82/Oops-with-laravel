<?php



namespace samarnas;



use Illuminate\Database\Eloquent\Model;



class Categorys extends Model

{

    protected $fillable = ['sub_category_id','sub_category_name','sub_category_name_arab','sub_category_amount','sub_category_time_limit','sub_category_image','service_id']; 



}