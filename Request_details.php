<?php
namespace samarnas;

use Illuminate\Database\Eloquent\Model;
use DB;
use samarnas\User;
use samarnas\ProviderServices;


class Request_details extends Model
{

    protected $fillable = ['request_id','user_id','booking_id','request_otp','request_flag']; 

    public function get_user_requestList($id)
    {
        $data = ProviderServices::select('service_id')->where('provider_user_id','=',$id)->get()->toArray();
        $data1 = array();
        foreach ($data as $key => $value) 
        {
            $data1[] = $value['service_id'];
        }
        // print_r("<pre>");
        // print_r($data1);
        // print_r("</pre>");
        // exit();
    	//return "happy";

        if(!empty($data1))
        {
            $table = "request_details";
            $join = "otherorderdatas";
        	$data = DB::table('request_details AS rd')
                        ->select('us.id as customer_id','us.fname','us.lname','us.profile_pic','oo.booking_id','rd.request_id','rd.id','oo.latitude','oo.longitude','oo.address_id','oo.address','us.mobile','cs.sub_category_name','cs.sub_category_image','oo.booked_time','oo.flag','oo.special_note')
                        ->join('otherorderdatas AS oo', function($join) use ($table)
                                {
                                    $join->on('rd.booking_id', '=',  'oo.booking_id');
                                    $join->on('rd.request_id','=', 'oo.sub_category_id');
                                })
                        ->join('users AS us','us.id','=','oo.user_id')
                        ->join('categorys AS cs','cs.sub_category_id','=','rd.request_id')
                        ->where('rd.user_id','=',$id)
                        ->where('oo.flag','!=',3)
                        ->whereIn('cs.service_id',$data1)
                        ->where('rd.request_flag','=',0)
                        ->where('rd.request_flag','!=',6)
                        ->orderBy('rd.id', 'DESC')
                        ->get()->toArray();
    	   return $data;
        }
        else
        {
            $data = array();
            return $data;
        }
    }

    public function get_customer_requestList($id)
    {

        //->select('us.id as provider_id','us.fname','us.lname','us.profile_pic','rd.booking_id','rd.request_id','rd.id','us.mobile','cs.sub_category_name','cs.sub_category_image','oo.booked_time','es.language','rd.request_otp','oo.latitude','oo.longitude')
                    

    	$data = DB::table('request_details AS rd')
                    ->select('us.id as provider_id','us.fname','us.lname','us.profile_pic','rd.booking_id','rd.request_id','rd.id','us.mobile','cs.sub_category_name','cs.sub_category_image','rd.request_otp')
                    ->join('bookings AS bs','bs.booking_id','=','rd.booking_id')
                    ->join('users AS us','us.id','=','rd.user_id')
                    ->join('categorys AS cs','cs.sub_category_id','=','rd.request_id')
                    //->join('otherorderdatas AS oo','oo.booking_id','=','rd.booking_id')
                   // ->join('extras AS es','es.provider_id','=','rd.user_id')
                    ->where('bs.user_id','=',$id)
                    //->where('oo.flag','!=',3)
                    ->where('rd.request_flag','=',0)
                    ->where('rd.request_flag','!=',6)
                    ->orderBy('rd.id', 'DESC')
                    ->get()->toArray();
        $data = json_decode(json_encode($data),true);
        $i = 0;
        $finaldata = array();
        foreach ($data as $key => $value) 
        {
            $data1 = array();
            $data1 = DB::table('otherorderdatas AS oo')
                    ->select('oo.booked_time','oo.latitude','oo.longitude','oo.user_id','oo.created_at')
                    ->where('oo.flag','!=',3)
                    ->where('oo.booking_id','=',$value['booking_id'])
                    ->where('oo.sub_category_id','=',$value['request_id'])
                    ->get()->toArray();
            $data1 = json_decode(json_encode($data1),true);  
            if(!empty($data1))   
            {
                $customerData = array();
                $customerData = User::where('id','=',$value['provider_id'])->first();
                // print_r("<pre>");
                // print_r($customerData->mobile);
                // print_r("</pre>");
                // exit();
                $data2 = DB::table('extras AS es')
                                ->select('es.language')
                                ->where('es.provider_id','=',$value['provider_id'])
                                ->get()->toArray();
                $data2 = json_decode(json_encode($data2));

                $finaldata[$i]['provider_id']           = $value['provider_id'];
                $finaldata[$i]['fname']                 = $value['fname'];
                $finaldata[$i]['lname']                 = $value['lname'];
                $finaldata[$i]['profile_pic']           = $value['profile_pic'];
                $finaldata[$i]['booking_id']            = $value['booking_id'];
                $finaldata[$i]['request_id']            = $value['request_id'];
                $finaldata[$i]['id']                    = $value['id'];
                $finaldata[$i]['mobile']                = (string)$customerData->mobile;
                $finaldata[$i]['sub_category_name']     = $value['sub_category_name'];
                $finaldata[$i]['sub_category_image']    = $value['sub_category_image'];
                //$finaldata[$i]['language'] = $value['language'];
                $finaldata[$i]['booked_time']           = $data1[0]['booked_time'];
                $finaldata[$i]['language']              = $data2[0]->language;
                $finaldata[$i]['request_otp']           = $value['request_otp'];
                $finaldata[$i]['latitude']              = $data1[0]['latitude'];
                $finaldata[$i]['longitude']             = $data1[0]['longitude'];
                $finaldata[$i]['time_flag']             = $data1[0]['created_at'];
                $i++;

            }
        }
            // print_r("<pre>");
            // print_r($finaldata);
            // print_r("</pre>");
            // exit();
                   
    	return $finaldata;
    }

    public function getLanguage($id)
    {
        $data = DB::table('language AS la')
                    ->select('language_name')
                    ->where('language_id','=',$id)
                    ->get()->toArray();
        return $data;
    }
}