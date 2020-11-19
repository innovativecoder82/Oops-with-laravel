<?php
 

namespace samarnas\Http\Controllers;
 
use Laravel\Passport\HasApiTokens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use samarnas\Notifications\SignupActivate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Stripe;
//use User;
use Stripe\Error\Card;
use samarnas\Request_details;
use samarnas\Carddetail;
use samarnas\User;
use samarnas\Notification;
use samarnas\Orderdetails;
use samarnas\Otherorderdatas;
use samarnas\Refunddetails;


class StripeController extends Controller
{
 
 public function create_token($number,$month,$year,$cvc)
    {
        
        $secret_key = 'sk_test_TrYPKJvaWHMlDKFbtFzI5IfE00ZDll1EbO';
 \Stripe\Stripe::setApiKey($secret_key);
        \Stripe\Stripe::setApiVersion("2019-09-09");
        try {

            $result = \Stripe\Token::create(array(
                        "card" => array(
                           'number' => $number,
    'exp_month' => $month,
    'exp_year' => $year,
    'cvc' => $cvc,
  
                        )
                    ));

            $return_data['status'] = true;
            $return_data['message'] = "Card saved successfully";
            $return_data['response'] = $result;
        }
        catch (Exception $e) {

            $return_data['status'] = false;
            $return_data['message'] = $e->getMessage();
        }

        return $return_data;
    }
     public function customer_cancel(Request $request)
    {
        
       $validator = Validator::make($request->all(), [
                'sub_category_id'   => 'required|min:1',
                'booking_id'        => 'required|min:1',
               
               
                
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
               
                $getlosts  = Request_details::select('user_id','request_flag')->where('booking_id','=', $request->booking_id)->where('request_id','=',$request->sub_category_id)->get()->first();
                if($getlosts['request_flag'] !='1')
                { 
                $tsts = count($getlosts); 
                if($tsts !='0')
                {
                    $user_id  = Auth::user()->id; 
                    $return_amount = Otherorderdatas::where([['sub_category_id','=', $request->sub_category_id],['booking_id', '=', $request->booking_id],['user_id','=', $user_id]])->get()->first();
                    $chargeid = Orderdetails::where([['booking_id', '=', $request->booking_id],['user_id','=', $user_id]])->get()->first();
                   // print_r($chargeid); exit();
                    $secret_key = 'sk_test_TrYPKJvaWHMlDKFbtFzI5IfE00ZDll1EbO';
                    \Stripe\Stripe::setApiKey($secret_key);
                    \Stripe\Stripe::setApiVersion("2019-09-09");
                    $result = \Stripe\Refund::create([
                          'charge' => $chargeid['payment_id'],
                          'amount' => $return_amount['sub_category_amount']
                        ]);
                        //print_r($result); exit();
                    if($result['status']== "succeeded") 
                        {
                            
                                    $user_id  = Auth::user()->id; 
                        		    $card = new Refunddetails([
                                    'payment_id'      => $chargeid['payment_id'],
                                    'refund_id'       => $result['id'],
                                    'booking_id'      => $request->booking_id,
                                    'user_id'         => $user_id,
                                    'sub_category_id' => $request->sub_category_id,
                                    'paid_amount'	  => $return_amount['sub_category_amount']
                                    
                                ]);
                                $card->save();
                                
                                return response()->json([
            			            'message' => 'Payment Successfully.',
            			            'status' => true,
            			            'status_code' => 200,
            			            'payment_id'      => $result['id']
                    			]);
                        }
                        else
                        {
                            
                            return response()->json([
            			            'message' => $result['message'],
            			            'status' => false,
            			            'status_code' => 422
                    			]);
                        }
                    
                    $user_data = User::where('id','=',$getlosts->user_id)->get()->toArray();
                    if(!empty($user_data))
                    {
                      if($user_data[0]['device_token'] != '' && $user_data[0]['device_type'] != '')
                      {
                        $receiver_id  = $getlosts->user_id;
                        # code...
                        $device = array(
                                  'is_apple'  => $user_data[0]['device_type'],
                                  'endpoint'  => $user_data[0]['device  _token']
                                );
                        $msg = array(
                              'title'     => "Notification for Samarnas",
                              'message'   => "Customer Cancelled the request",
                              'user_type' => "provider",
                              'type'      => "customer_cancel"
                            );
                        $notify = new Notification();
                        $dd = $notify->notification($device,$msg);
                        $saveNotify   = new Notification([
                                          'sender_id'     => $user_id,
                                          'receiver_id'   => $receiver_id,
                                          'title'         => "customer_cancel",
                                          'message'       => json_encode($msg),
                                          'type'          => "customer_cancel",
                                          'status'        => 0
                        ]);
                        $saveNotify->save();
                      }
                    }
                    $getdetails = Otherorderdatas::where([['sub_category_id','=', $request->sub_category_id],['booking_id', '=', $request->booking_id],['user_id','=', $user_id]])->update(['flag'=> '6']);
                    $requestdetails = Request_details::where([['request_id','=', $request->sub_category_id],['booking_id', '=', $request->booking_id],['user_id','=', $getlosts->user_id]])->update(['request_flag'=> '6']);
                    return response()->json([
                            'message' => 'Request Cancelled successfully.',
                            'status' => true,
                            'status_code' => 200
                        ]);
                }
                else
                {
                    $user_id  = Auth::user()->id;
                    $getdetails = Otherorderdatas::where([['sub_category_id','=', $request->sub_category_id],['booking_id', '=', $request->booking_id],['user_id','=', $user_id]])->update(['flag'=> '6']);
                
                        return response()->json([
                            'message' => 'Request Cancelled successfully.',
                            'status' => true,
                            'status_code' => 200
                        ]);
                }
                }
                else
                {
                        return response()->json([
                            'message' => 'Request Already Cancelled.',
                            'status' => true,
                            'status_code' => 200
                        ]);
                }
                
            }
    }
    public function payment(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
                'card_id'       => 'required',
                'total_amount'  => 'required',
                'currency'      => 'required',
                'cvv'           => 'required'
                
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
        
            
                $card_id = $request->card_id;
                $total_amount = $request->total_amount;
                $currency = $request->currency;
                $cvv = $request->cvv;
                $user_id  = Auth::user()->id; 
                $infoArray = Carddetail::where([['card_id','=', $card_id],['user_id','=', $user_id]])->get()->first();
                $name = User::select('fname')->where('id','=', $user_id)->get()->first();
                
                $datas =  StripeController::create_token($infoArray['card_number'],$infoArray['exp_month'],$infoArray['exp_year'],$cvv);
                
                if($datas['status'])
                {
                     try {
                    $amount = number_format($total_amount,2,'.','') * 100; // 188531.81
   
                $result = \Stripe\Charge::create(array(
                              "amount" => $amount,
                              "currency" => 'usd',
                              "source" => $datas['response']->id, // obtained with Stripe.js
                              "description" => "Charge from ".$name['fname']
                            ));
                           // print_r($result); exit();
                        if($result['status']== "succeeded") 
                        {
                            
                                    $user_id  = Auth::user()->id; 
                        		    $card = new Orderdetails([
                                    'payment_id'      => $result['id'],
                                    'payment_date'    => date("Y-m-d", strtotime($result['created'])),
                                    'payment_token'   => $datas['response']->id,
                                    'stripe_status'   => $result['status'],
                                    'totalamount'     => $amount,
                                    'user_id'	      => $user_id
                                    
                                ]);
                                $card->save();
                                
                                return response()->json([
            			            'message' => 'Payment Successfully.',
            			            'status' => true,
            			            'status_code' => 200,
            			            'payment_id'      => $result['id']
                    			]);
                        }
                        else
                        {
                            
                            return response()->json([
            			            'message' => $result['message'],
            			            'status' => false,
            			            'status_code' => 422
                    			]);
                        }
                    }
           catch(\Stripe\Exception\CardException $e) {
  
                    return response()->json([
            			            'message' => $e->getError()->message,
            			            'status' => $e->getError()->type,
            			            'status_code' => $e->getHttpStatus()
                    			]);
  
  
  
  
} catch (\Stripe\Exception\RateLimitException $e) {
  return response()->json([
            			            'message' => $e->getError()->message,
            			            'status' => $e->getError()->type,
            			            'status_code' => $e->getHttpStatus()
                    			]);
} catch (\Stripe\Exception\InvalidRequestException $e) {
  return response()->json([
            			            'message' => $e->getError()->message,
            			            'status' => $e->getError()->type,
            			            'status_code' => $e->getHttpStatus()
                    			]);
} catch (\Stripe\Exception\AuthenticationException $e) {
  return response()->json([
            			            'message' => $e->getError()->message,
            			            'status' => $e->getError()->type,
            			            'status_code' => $e->getHttpStatus()
                    			]);
} catch (\Stripe\Exception\ApiConnectionException $e) {
  return response()->json([
            			            'message' => $e->getError()->message,
            			            'status' => $e->getError()->type,
            			            'status_code' => $e->getHttpStatus()
                    			]);
} catch (\Stripe\Exception\ApiErrorException $e) {
  return response()->json([
            			            'message' => $e->getError()->message,
            			            'status' => $e->getError()->type,
            			            'status_code' => $e->getHttpStatus()
                    			]);
} catch (Exception $e) {
  return response()->json([
            			            'message' => $e->getError()->message,
            			            'status' => $e->getError()->type,
            			            'status_code' => $e->getHttpStatus()
                    			]);
}
                }
                else
                {
                    return response()->json([
    			            'message' => $datas['message'],
    			            'status' => false,
    			            'status_code' => 422
            			]);
                
                }
            
            }
        
       
      
    }

 
 
 
 
 public function payment1(Request $request)
    {
                  
        $validator = Validator::make($request->all(), [
                'card_id'       => 'required',
                'total_amount'  => 'required',
                'currency'      => 'required',
                'cvv'           => 'required'
                
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
        
            
                $card_id = $request->card_id;
                $total_amount = $request->total_amount;
                $currency = $request->currency;
                $cvv = $request->cvv;
                $user_id  = Auth::user()->id; 
                $infoArray = Carddetail::where([['card_id','=', $card_id],['user_id','=', $user_id]])->get()->first();
                $name = User::select('fname')->where('id','=', $user_id)->get()->first();
                
                $datas =  StripeController::create_token($infoArray['card_number'],$infoArray['exp_month'],$infoArray['exp_year'],$cvv);
                
                if($datas['status'])
                {
                    try {
                    $amount = number_format($total_amount,2,'.','') * 100; // 188531.81
   
                $result = \Stripe\Charge::create(array(
                              "amount" => $amount,
                              "currency" => 'usd',
                              "source" => $datas['response']->id, // obtained with Stripe.js
                              "description" => "Charge from ".$name['fname']
                            ));
                            print_r($result); exit();
                        if($result['status']== "succeeded") 
                        {
                            
                                    $user_id  = Auth::user()->id; 
                        		    $card = new Orderdetails([
                                    'payment_id'      => $result['id'],
                                    'payment_date'    => date("Y-m-d", strtotime($result['created'])),
                                    'payment_token'   => $datas['response']->id,
                                    'stripe_status'   => $result['status'],
                                    'user_id'	      => $user_id
                                    
                                ]);
                                $card->save();
                                
                                return response()->json([
            			            'message' => 'Payment Successfully.',
            			            'status' => true,
            			            'status_code' => 200
                    			]);
                        }
                        else
                        {
                            
                            return response()->json([
            			            'message' => $result['message'],
            			            'status' => false,
            			            'status_code' => 422
                    			]);
                        }
                    }
           catch(\Stripe\Exception\CardException $e) {
  
                    return response()->json([
            			            'message' => $e->getError()->message,
            			            'status' => $e->getError()->type,
            			            'status_code' => $e->getHttpStatus()
                    			]);
  
  
  
  
} catch (\Stripe\Exception\RateLimitException $e) {
  return response()->json([
            			            'message' => $e->getError()->message,
            			            'status' => $e->getError()->type,
            			            'status_code' => $e->getHttpStatus()
                    			]);
} catch (\Stripe\Exception\InvalidRequestException $e) {
  return response()->json([
            			            'message' => $e->getError()->message,
            			            'status' => $e->getError()->type,
            			            'status_code' => $e->getHttpStatus()
                    			]);
} catch (\Stripe\Exception\AuthenticationException $e) {
  return response()->json([
            			            'message' => $e->getError()->message,
            			            'status' => $e->getError()->type,
            			            'status_code' => $e->getHttpStatus()
                    			]);
} catch (\Stripe\Exception\ApiConnectionException $e) {
  return response()->json([
            			            'message' => $e->getError()->message,
            			            'status' => $e->getError()->type,
            			            'status_code' => $e->getHttpStatus()
                    			]);
} catch (\Stripe\Exception\ApiErrorException $e) {
  return response()->json([
            			            'message' => $e->getError()->message,
            			            'status' => $e->getError()->type,
            			            'status_code' => $e->getHttpStatus()
                    			]);
} catch (Exception $e) {
  return response()->json([
            			            'message' => $e->getError()->message,
            			            'status' => $e->getError()->type,
            			            'status_code' => $e->getHttpStatus()
                    			]);
}
                }
                else
                {
                    return response()->json([
    			            'message' => $datas['message'],
    			            'status' => false,
    			            'status_code' => 422
            			]);
                
                }
            
            }
        
        
}

public function createcustomer(Request $request)
    {
                 
        $apiKey = 'sk_test_TrYPKJvaWHMlDKFbtFzI5IfE00ZDll1EbO';
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => "https://api.stripe.com/v1/customers",
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $apiKey
            ],
            CURLOPT_POSTFIELDS => http_build_query([
                "email" => "yogesha.a@pickzy.com",
                "description" => "testing"
            ]) 
        ]);
        $resp = curl_exec($curl);
        $restz = json_decode($resp);
       
       $array = get_object_vars($restz);
       
        curl_close($curl);
        
        $apiKey = 'sk_test_TrYPKJvaWHMlDKFbtFzI5IfE00ZDll1EbO';
        $curl = curl_init();
        $ids = $array['id'];
      /* echo "https://api.stripe.com/v1/customers/.$ids./sources";
       exit();*/
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => "https://api.stripe.com/v1/customers/$ids/sources",
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $apiKey
            ],
            CURLOPT_POSTFIELDS => http_build_query([
                
                "source" => "tok_visa",
                
            ])
        ]);
        $respz = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $respz;
        }

}

}