<?php

namespace samarnas\Http\Controllers;
use Laravel\Passport\HasApiTokens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use samarnas\Notifications\SignupActivate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use samarnas\Categorys;
use samarnas\Addtocarts;
use samarnas\Price;
use samarnas\Tracking;
use DB;
class AddCartController extends Controller
{
    public function addtocart(Request $request)
    {
    	$validator = Validator::make($request->all(), [
                'sub_category_id'   => 'required|min:1',
                'gender_id'         => 'required|min:1',
                'service_id'        => 'required',
                'booked_time'       => 'required|min:2'
                //'sub_category_name'  => 'required|min:5',
               // 'sub_category_amount'   	=> 'required|min:3'
                
            ]);
    	if($validator->fails()) {
            
                $message = $validator->errors();
                $array = json_decode(json_encode($message), true);
                foreach($array as $key => $check)
                {
                    return response()->json([
                        'message' => $check['0'],
                        'status' => false,
                        'status_code' => 422
                    ]);
                }
                
            }
            else
            {
                
                $time = strtotime($request->booked_time); 
                $booked_time = date('Y-m-d H:i:s', $time);
                $booked_date = date('Y-m-d', $time);
                  
                $countsadd = Addtocarts::where('user_id', '=', $request->user_id)->whereDate('booked_time','=',$booked_date)->get()->toArray();
                //print_r($countsadd); exit();
                $countsaddz = Categorys::select('sub_category_time_limit')->where('sub_category_id', '=', $request->sub_category_id)->get()->first();
                $sub_category_time_limit = $countsaddz['sub_category_time_limit'];
                if(!empty($countsadd))
                {
                    foreach($countsadd as $countsadds)
                    {
                        $seconds  = strtotime($booked_time) - strtotime($countsadds['booked_time']);
                        $mins     = round($seconds / 60);
                        if($mins<$sub_category_time_limit)
                        {
                            $flag = 1;
                        }
                        else
                        {
                            $flag = 0;
                        }
                    }
                }
                else
                {
                    $flag = 0;
                }
                if($flag!=0)
                {
                
                
                    return response()->json([
                        'message' => 'booking already set same time. please set different time',
                        'status' => true,
                        'status_code' => 412
                    ]);
                }
                
                else
                {
                    //echo $booked_time; exit();
                    $getdetails = Categorys::where('sub_category_id','=', $request->sub_category_id)->get()->toArray();
                    $amountlist = Price::where([['service_id','=', $request->sub_category_id],['country_id', '=', 1]])->get()->toArray();
                    //print_r($getdetails); exit();
                    $card = new Addtocarts([
                    'sub_category_id'       => $request->sub_category_id,
                    'sub_category_name'     => $getdetails[0]['sub_category_name'],
                    'sub_category_amount'   => $amountlist[0]['amount'],
                    'sub_category_image'    => $getdetails[0]['sub_category_image'],
                    'user_id'               => $request->user_id,
                    'gender_id'             => $request->gender_id,
                    'service_id'            => $request->service_id,
                    'booked_time'           => $booked_time
                        
                    ]);
                   // print_r($card); exit();
                    $card->save();
                    return response()->json([
                        'message' => 'Cart added successfully.',
                        'status' => true,
                        'status_code' => 200
                    ]);
                }
                
            
            }
	}
	public function getcartdetailsbyuser(Request $request)
    {
        $infoArray = [];
        $user_id = $request->user_id;
        $infoArray = Addtocarts::where('user_id','=', $user_id)->get()->toArray();
       // print_r($infoArray); exit();
        if(!empty($infoArray))
        {
       // 	$total=[];
		$total = Addtocarts::where('user_id', '=', $user_id)->sum('sub_category_amount');
            foreach ($infoArray as $key => $value) 
            {
               $infoArrays[$key]['sub_category_id']     = $value['sub_category_id'];
               $infoArrays[$key]['sub_category_name']   = $value['sub_category_name'];
               $infoArrays[$key]['sub_category_amount'] = "AED ".$value['sub_category_amount'];
               $infoArrays[$key]['booked_time']         = $value['booked_time'];
               $infoArrays[$key]['gender_id']           = $value['gender_id'];
               $infoArrays[$key]['sub_category_image']  = $value['sub_category_image'];
               //$total = $value['sub_category_amount'];
            }

        return response()->json([
            'message' => 'Cart details showed successful!',
            'status' => true,
            'status_code' => 200,
            'result' => $infoArrays,
            'total' => $total
        ]); 
        }
        else{
           return response()->json([
            'message' => 'Cart details showed successful!',
            'status' => true,
            'status_code' => 200,
            'result' => $infoArray
           
        ]); 
        } 
    }
    
    public function removefromcart(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'sub_category_id'   => 'required|min:1',
                'user_id'  => 'required|min:1',
               // 'sub_category_amount'     => 'required|min:3'
                
            ]);
        if($validator->fails()) {
            
                $message = $validator->errors();
                $array = json_decode(json_encode($message), true);
                foreach($array as $key => $check)
                {
                    return response()->json([
                        'message' => $check['0'],
                        'status' => false,
                        'status_code' => 422
                    ]);
                }
                
            }
            else
            {
            $getdetails = Addtocarts::where([['sub_category_id','=', $request->sub_category_id],['user_id', '=', $request->user_id]]);
            //print_r($getdetails); exit();
                   
                    $getdetails->delete();
                    return response()->json([
                        'message' => 'Cart item deleted successfully.',
                        'status' => true,
                        'status_code' => 200
                    ]);
            }
    }
    public function removeallfromcart(Request $request)
    {
        $validator = Validator::make($request->all(), [
                
                'gender_id' => 'required'
            ]);
        if($validator->fails()) {
            
                $message = $validator->errors();
                $array = json_decode(json_encode($message), true);
                foreach($array as $key => $check)
                {
                    return response()->json([
                        'message' => $check['0'],
                        'status' => false,
                        'status_code' => 422
                    ]);
                }
                
            }
            else
            {
                $user_id = auth()->user()->id;
                $gender_id = $request->gender_id; 
                $getdetailszz = Addtocarts::where('user_id', '=', $user_id)->get()->first();
                //echo count($getdetailszz); exit();
                if(count($getdetailszz)!='0')
                {
                //if(count($getdetailszz))
                if($getdetailszz['gender_id']!=$gender_id)
                {
                    $getdetails = Addtocarts::where('user_id',$user_id);
                    //print_r($getdetails); exit();
                   
                    $getdetails->delete();
                    return response()->json([
                        'message' => 'Cart cleared successfully.',
                        'status' => true,
                        'status_code' => 200
                    ]);
                }
                else
                {
                    return response()->json([
                        'message' => 'You can able to add item in cart',
                        'status' => true,
                        'status_code' => 200
                    ]);
                }
                }
                else
                {
                    return response()->json([
                        'message' => 'No Items added in cart',
                        'status' => true,
                        'status_code' => 200
                    ]);
                }
                
            }
    }
public function clearallfromcart(Request $request)
    {
        
                $user_id = auth()->user()->id;
                $gender_id = $request->gender_id; 
                $getdetailszz = Addtocarts::where('user_id', '=', $user_id)->get()->first();
                //echo count($getdetailszz); exit();
                if(count($getdetailszz)!='0')
                {
                
                
                    $getdetails = Addtocarts::where('user_id',$user_id);
                    
                    $getdetails->delete();
                    return response()->json([
                        'message' => 'Cart cleared successfully.',
                        'status' => true,
                        'status_code' => 200
                    ]);
                
                    
                
                }
                else
                {
                    return response()->json([
                        'message' => 'No Items added in cart',
                        'status' => true,
                        'status_code' => 200
                    ]);
                }
                
            
    }
    public function cartcount(Request $request)
    {
        $user_id = auth()->user()->id;
        $getdetails = Addtocarts::where('user_id', '=', $user_id)->get()->toArray();
        if(!empty($getdetails))
        {
            return response()->json([
                'message'       => 'Cart count successfully.',
                'cart_count'    => (string)(count($getdetails)),
                'status'        => true,
                'status_code'   => 200
            ]);
        }
        else
        {
             return response()->json([
                'message'       => 'Cart count successfully.',
                'cart_count'    => "0",
                'status'        => true,
                'status_code'   => 200
            ]);
        }
    }
   
    public function updatelocation(Request $request)
    {
        $user_id = auth()->user()->id;  
        //echo $request->latitude; exit();
        $checkspro = Tracking::where('provider_id','=',$user_id)->get()->first();
        if(!empty($checkspro))
        {
            $user = Tracking::where('provider_id','=',$user_id)->update(['latitude' => $request->latitude,'longitude' => $request->longitude]);  
       
        return response()->json([
            'message' => 'Latitude and Longitude updated successfully.',
            'status' => true,
            'status_code' => 200
        ]);
        }
        else
        {
            $track = new Tracking([
                    'latitude'      => $request->latitude,
                    'longitude'     => $request->longitude,
                    'provider_id'   => $user_id
                    ]);
                   
            $track->save();
            return response()->json([
                        'message' => 'Latitude and Longitude inserted successfully.',
                        'status' => true,
                        'status_code' => 200
                    ]);
        }
        
        
    }
    public function gettrackdetail(Request $request)
    {
        $infoArray = [];		
        $user_id  = Auth::user()->id; 
        $infoArray = Tracking::where([['provider_id','=',$user_id],['id', '=', $id]])->get()->toArray();
        //print_r($infoArray); exit();
        if(!empty($infoArray))
        {
          foreach ($infoArray as $key => $value) 
          {
           $getcarts = Otherorderdatas::where([['sub_category_id','=', $value['sub_category_id']],['user_id', '=', $user_id]])->get()->toArray();
          $infoArrays[$key]['sub_category_id'] = $value['sub_category_id'];
          $infoArrays[$key]['sub_category_name'] = $value['sub_category_name'];
         // $infoArrays[$key]['sub_category_amount'] = $value['sub_category_amount'];
          $infoArrays[$key]['sub_category_image'] = "http://temp.pickzy.com/samarnas/storage/app/public/categorysimage/".$value['sub_category_image'];
          $infoArrays[$key]['sub_category_time_limit'] = $value['sub_category_time_limit'];
          }

        return response()->json([
            'message' => trans('messages.category'),
            'status' => true,
            'status_code' => 200,
            'result' => $infoArrays
        ]); 
        }
    }

    public function getlocation(Request $request)
    {
        $user_id = $request->provider_id; 
       // echo $user_id; exit();
        $checkspro = Tracking::select('latitude','longitude')->where('provider_id','=',$user_id)->get()->first();
        // print_r($checkspro) ;
        // exit();
        if(!empty($checkspro))
        {
            //$user = Tracking::where('provider_id','=',$user_id)->update(['latitude' => $request->latitude,'longitude' => $request->longitude]);  
       
        return response()->json([
            'message' => 'Successfully get Latitude and Longitude',
            'data'  => $checkspro,
            'status' => true,
            'status_code' => 200
        ]);
        }
        else
        {
            // $track = new Tracking([
            //         'latitude'      => $request->latitude,
            //         'longitude'     => $request->longitude,
            //         'provider_id'   => $user_id
            //         ]);
                   
            // $track->save();
            $checkspros = array();
            return response()->json([
                        'message' => 'No data found',
                        'data'  => $checkspros,
                        'status' => true,
                        'status_code' => 422
                    ]);
        }
        
        
    }
    
}
