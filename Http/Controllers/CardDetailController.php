<?php

namespace samarnas\Http\Controllers;
use Laravel\Passport\HasApiTokens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use samarnas\Notifications\SignupActivate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use samarnas\Carddetail;
use samarnas\Providerbanks;


class CardDetailController extends Controller
{
    public function insertcard(Request $request)
    {
    	$validator = Validator::make($request->all(), [
                'card_number'   => 'required|min:16',
                'exp_year'  => 'required|min:1',
                'exp_month'  => 'required|min:1',
                'cvv'   	=> 'required|min:3',
                'country'   => 'required|string',
                'card_type' => 'required|string'
                
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
                    $user_id  = Auth::user()->id; 
            		$card = new Carddetail([
                        'card_number'  => $request->card_number,
                        'exp_month'     => $request->exp_month,
                        'exp_year'     => $request->exp_year,
                        'cvv'          => $request->cvv,
                        'country'      => $request->country,
                        'card_type'    => $request->card_type,
                        'user_id'	   => $user_id
                        
                    ]);
                    $card->save();
                    
                    return response()->json([
			            'message' => 'Card details added successfully.',
			            'status' => true,
			            'status_code' => 200
        			]);
            }
    }
    public function insertbank(Request $request)
    {
    	$validator = Validator::make($request->all(), [
                'account_name'   => 'required',
                'account_number' => 'required',
                'ifsc_code'   	 => 'required'
                
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
                    $provider_id  = Auth::user()->id; 
            		$card = new Providerbanks([
                        'account_name'   => $request->account_name,
                        'provider_id'    => $provider_id,
                        'account_number' => $request->account_number,
                        'ifsc_code'      => $request->ifsc_code,
                        
                    ]);
                    $card->save();
                    
                    return response()->json([
			            'message' => 'Bank details added successfully.',
			            'status' => true,
			            'status_code' => 200
        			]);
            }
    }
    public function getbankdetails(Request $request)
    {
        $infoArray = [];
        $bank_id = $request->bank_id;
        $infoArray = Providerbanks::where('id','=', $bank_id)->get()->toArray();
       // print_r($infoArray); exit();
        if(!empty($infoArray))
        {
            foreach ($infoArray as $key => $value) 
            {
               $infoArrays[$key]['account_name']  = $value['account_name'];
               $infoArrays[$key]['provider_id']  = $value['provider_id'];
               $infoArrays[$key]['account_number']  = $value['account_number'];
               $infoArrays[$key]['ifsc_code']  = $value['ifsc_code'];
               
            }

        return response()->json([
            'message' => 'Bank detail showed successful!',
            'status' => true,
            'status_code' => 200,
            'result' => $infoArrays
        ]); 
        }
        else{
            return response()->json([
            'message' => 'Bank detail showed successful!',
            'status' => true,
            'status_code' => 200,
            'result' => $infoArray
        ]); 
        } 
    }
    public function getcarddetails(Request $request)
    {
        $infoArray = [];
        $card_id = $request->card_id;
        $infoArray = Carddetail::where('card_id','=', $card_id)->get()->toArray();
       // print_r($infoArray); exit();
        if(!empty($infoArray))
        {
            foreach ($infoArray as $key => $value) 
            {
               $infoArrays[$key]['card_number']  = $value['card_number'];
               $infoArrays[$key]['exp_month']  = $value['exp_month'];
               $infoArrays[$key]['exp_year']  = $value['exp_year'];
               $infoArrays[$key]['cvv']  = $value['cvv'];
               $infoArrays[$key]['card_type'] = $value['card_type'];
               $infoArrays[$key]['country']    = $value['country'];
               
            }

        return response()->json([
            'message' => 'Carddetail showed successful!',
            'status' => true,
            'status_code' => 200,
            'result' => $infoArrays
        ]); 
        }
        else{
            return response()->json([
            'message' => 'Carddetail showed successful!',
            'status' => true,
            'status_code' => 200,
            'result' => $infoArray
        ]); 
        } 
    }
    public function removefrombank(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'bank_id'   => 'required|min:1',
               
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
                 $provider_id  = Auth::user()->id; 
            $getdetails = Providerbanks::where([['id','=', $request->bank_id],['provider_id', '=', $provider_id]]);
            //print_r($getdetails); exit();
                   
                    $getdetails->delete();
                    return response()->json([
                        'message' => 'Bank data deleted successfully.',
                        'status' => true,
                        'status_code' => 200
                    ]);
            }
    }
    public function removefromcard(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'card_id'   => 'required|min:1',
               // 'user_id'  => 'required|min:1',
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
                 $user_id  = Auth::user()->id; 
            $getdetails = Carddetail::where([['card_id','=', $request->card_id],['user_id', '=', $user_id]]);
            //print_r($getdetails); exit();
                   
                    $getdetails->delete();
                    return response()->json([
                        'message' => 'Card item deleted successfully.',
                        'status' => true,
                        'status_code' => 200
                    ]);
            }
    }
    public function getallbankbyuser(Request $request)
    {
        $infoArray = [];
        $provider_id  = Auth::user()->id; 
        $infoArray = Providerbanks::where('provider_id','=', $provider_id)->get()->toArray();
       // print_r($infoArray); exit();
        if(!empty($infoArray))
        {
            foreach ($infoArray as $key => $value) 
            {
               $infoArrays[$key]['bank_id']  = $value['id'];
               $infoArrays[$key]['account_name']  = $value['account_name'];
               $infoArrays[$key]['provider_id']  = $value['provider_id'];
               $infoArrays[$key]['account_number']  = $value['account_number'];
               $infoArrays[$key]['ifsc_code']  = $value['ifsc_code'];
               
            }

        return response()->json([
            'message' => 'Bank List showed successful!',
            'status' => true,
            'status_code' => 200,
            'result' => $infoArrays
        ]); 
        }
        else{
           return response()->json([
            'message' => 'Bank List showed successful!',
            'status' => true,
            'status_code' => 200,
            'result' => $infoArray
        ]); 
        } 
    }
    public function getallcardbyuser(Request $request)
    {
        $infoArray = [];
        $user_id  = Auth::user()->id; 
        $infoArray = Carddetail::where('user_id','=', $user_id)->get()->toArray();
       // print_r($infoArray); exit();
        if(!empty($infoArray))
        {
            foreach ($infoArray as $key => $value) 
            {
               $infoArrays[$key]['card_id']  = $value['card_id'];
               $infoArrays[$key]['card_number']  = $value['card_number'];
               $infoArrays[$key]['exp_month']  = $value['exp_month'];
               $infoArrays[$key]['exp_year']  = $value['exp_year'];
               $infoArrays[$key]['cvv']  = $value['cvv'];
               $infoArrays[$key]['card_type'] = $value['card_type'];
               $infoArrays[$key]['country']    = $value['country'];
               
            }

        return response()->json([
            'message' => 'Carddetail showed successful!',
            'status' => true,
            'status_code' => 200,
            'result' => $infoArrays
        ]); 
        }
        else{
           return response()->json([
            'message' => 'Carddetail showed successful!',
            'status' => true,
            'status_code' => 200,
            'result' => $infoArray
        ]); 
        } 
    }
}
