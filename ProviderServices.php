<?php

namespace samarnas;

use Illuminate\Database\Eloquent\Model;
use Eloquent;
use DB;

class ProviderServices extends Model
{
    protected $fillable = ['service_id','provider_user_id','gender_type_id']; 

    public function getSubCategory($id,$requestdata = NULL)
    {
        if($requestdata->header('X-localization') == 'ar')
        {
    	   $data = DB::table('provider_services AS ps')->select('ss.service_id','ss.service_name_arab as service_name','ss.image')->join('users AS us','us.id','=','ps.provider_user_id')->join('services AS ss','ss.service_id','=','ps.service_id')->where('ps.provider_user_id', '=', $id)->get()->toArray();
        }
        else
        {
            $data = DB::table('provider_services AS ps')->select('ss.service_id','ss.service_name','ss.image')->join('users AS us','us.id','=','ps.provider_user_id')->join('services AS ss','ss.service_id','=','ps.service_id')->where('ps.provider_user_id', '=', $id)->get()->toArray();
        }
    	$data = json_decode(json_encode($data),true);
       
        //$j= 0;
        //print_r($data); exit();
    	foreach ($data as $key => $value) 
    	{
    		$serId 				= $value['service_id'];
            if($requestdata->header('X-localization') == 'ar')
            {
    		  $subCategoryData 	= DB::table('categorys AS cs')->select('cs.sub_category_name_arab as sub_category_name','cs.sub_category_id','cs.sub_category_image')->where('cs.service_id','=',$serId)->get()->toArray();
            }
            else
            {
                $subCategoryData    = DB::table('categorys AS cs')->select('cs.sub_category_name','cs.sub_category_id','cs.sub_category_image')->where('cs.service_id','=',$serId)->get()->toArray();
            }
    		$genderdata = DB::table('gender_services')->select('gender_id')->where('service_id','=',$serId)->get()->toArray();
            $subCategoryData = json_decode(json_encode($subCategoryData),true);
            //print_r($subCategoryData); exit();
            if(!empty($subCategoryData))
            {
                //$i = 0;
                foreach ($subCategoryData as $key1 => $value1) 
                {
                    $subCategoryData[$key1]['test'] = '';
                    $check = DB::table('provider_subcategory')->where([['sub_category_id','=',$value1['sub_category_id']],['provider_id','=',$id]])->get()->toArray();
                    if(!empty($check))
                    {
                      $subCategoryData[$key1]['isChecked']  = true;
                    }
                    else
                    {
                      $subCategoryData[$key1]['isChecked']  = false;
                    }
                    //$i++;
                }
            }
    		$data[$key]['subCategoryData'] = $subCategoryData;
            $data[$key]['gender_data'] = $genderdata;
    		//dd($value->service_id);
    	}
    	return $data;
    }
    
   public function getservice($id)
   {
    $data = DB::table('provider_services AS ps')->select('ss.service_id','ss.service_name')->join('users AS us','us.id','=','ps.provider_user_id')->join('services AS ss','ss.service_id','=','ps.service_id')->where('ps.provider_user_id', '=', $id)->get()->toArray();
    $data = json_decode(json_encode($data),true);
    return $data;  // print_r($data); exit();
   }

}