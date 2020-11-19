<?php

namespace samarnas\Http\Controllers;

use Laravel\Passport\HasApiTokens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use samarnas\Notifications\SignupActivate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use samarnas\Bookings;
use samarnas\Extras;
use samarnas\Otherorderdatas;
use samarnas\Orderdetails;
use Carbon\Carbon;
use samarnas\User;
use samarnas\Notification;
use DB;
use samarnas\Addtocarts;
use samarnas\Categorys;
use samarnas\Locations;
use samarnas\Request_details;
use samarnas\ProviderServices;
use samarnas\Services_processes;
class BookingController extends Controller
{
    public function booking(Request $request)
    {
                   
        //return $request->address_id;
    		//date_default_timezone_set('Asia/Kolkata');
    		$booking_status = 'complete';
    		$mytime = Carbon::now();
            //$schedule_later = $mytime->toDateTimeString(); 
            //$schedule_later = $request->booking_time;
            $address_id     = $request->address_id;
            $latitude       = $request->latitude;
            $longitude      = $request->longitude;
            $payment_id     = $request->payment_id;
            // $haversine = "(6371 * acos(cos(radians(" . $latitude . ")) 
            //                   * cos(radians(`latitude`)) 
            //                   * cos(radians(`longitude`) 
            //                   - radians(" . $longitude . ")) 
            //                   + sin(radians(" . $latitude . ")) 
            //                   * sin(radians(`latitude`))))";
            //       $radius = 5;
            //       $infoArray = Extras::select('provider_id')->selectRaw("{$haversine} AS distance")->whereRaw("{$haversine} < ?", [$radius])->get()->toArray();
           // $time           = strtotime($schedule_later); // timestamp from RFC 2822
          //  $schedules      = date('Y-m-d H:i:s', $time);
             

            $user_id  = Auth::user()->id; 
            
            $address  = Locations::select('fulladdress')->where([['user_id','=', $user_id],['address_id','=', $address_id]])->get()->first();
    		$booking = new Bookings([
                        'user_id'           => $user_id,
                        'booking_status'    => $booking_status,
                        //'schedule_later'    => $schedules,
                        'address_id'        => $address_id,
                        'address'           => $address['fulladdress'],
                        'latitude'          => $latitude,
                       	'longitude'         => $longitude
                        
                    ]);
            $booking->save();
            //return "hi";
                     $booking_id  = $booking->id; 

            $orderDetails = Orderdetails::where('payment_id','=', $payment_id)->update(['booking_id'=> $booking_id]);
            $infoArrayz = Addtocarts::where('user_id','=', $user_id)->get()->toArray();

           // print_r($infoArrayz); exit();
                   foreach ($infoArrayz as $key => $value) {
                       //$address  = Locations::select('fulladdress')->where([['user_id','=', $user_id],['address_id','=', $address_id]])->get()->first();
                       $order = new Otherorderdatas([
                        'sub_category_id' => $value['sub_category_id'],
                        'sub_category_name' => $value['sub_category_name'],
                        'sub_category_amount' => $value['sub_category_amount'],
                        //'sub_category_amount' => $value['sub_category_amount'],
                        'booked_time' => $value['booked_time'],
                        'user_id' => $user_id,
                        'booking_id' => $booking_id,
                        'address_id'        => $address_id,
                        'address'           => $address['fulladdress'],
                        'latitude'          => $latitude,
                       	'longitude'         => $longitude
                    ]);
                    $order->save(); 
                    $serviceIdArray[]     = $value['service_id'];
                   }
                   $serviceIdArray =array_unique($serviceIdArray);
                    DB::table('addtocarts')->where('user_id', '=', $user_id)->delete();

                    // $lat   = $user_data[0]['latitude'];
                    // $lng   = $user_data[0]['longitude']; 
                  // $booking = Bookings::all();
                  $haversine = "(6371 * acos(cos(radians(" . $latitude . ")) 
                              * cos(radians(`latitude`)) 
                              * cos(radians(`longitude`) 
                              - radians(" . $longitude . ")) 
                              + sin(radians(" . $latitude . ")) 
                              * sin(radians(`latitude`))))";
                  $radius = 15;
                  $infoArray = Extras::select('provider_id')->selectRaw("{$haversine} AS distance")->whereRaw("{$haversine} < ?", [$radius])->get()->toArray();

                  if(!empty($infoArray))
                  {
                    foreach ($infoArray as $key => $value) 
                    {
                      $users = User::where('id','=',$value['provider_id'])->get()->toArray();

                      $data = ProviderServices::select('service_id')->where('provider_user_id','=',$value['provider_id'])->get()->toArray();
                      foreach ($data as $key2 => $value2) 
                      {
                        # code...
                        if(in_array($value2['service_id'], $serviceIdArray))
                        {
                          if(!empty($users))
                          {
                            if($users[0]['device_token'] != '' && $users[0]['device_type'] != '')
                            {
                              $receiver_id  = $value['provider_id'];
                              # code...
                              $device = array(
                                        'is_apple'  => $users[0]['device_type'],
                                        'endpoint'  => $users[0]['device_token']
                                      );
                              $msg = array(
                                    'title'           => "Notification for Samarnas",
                                    'message'         => "New Order Request from",
                                    'user_type'       => "provider",
                                    'sub_category_id' => 0,
                                    'type'            => "1"
                                  );
                              $notify = new Notification();
                              $dd = $notify->notification($device,$msg);
                              $saveNotify   = new Notification([
                                                'sender_id'     => $user_id,
                                                'receiver_id'   => $receiver_id,
                                                'title'         => "book_now",
                                                'message'       => json_encode($msg),
                                                'type'          => "book_now",
                                                'status'        => 0
                              ]);
                              $saveNotify->save();
                            }
                          }
                          break;
                        }
                      }
                  
                    }
                  }
                    return response()->json([
			            'message' => 'Order Placed Successfully.',
			            'status' => true,
			            'status_code' => 200
        			]);
    }
    public function schedule(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'schedule_later'   => 'required|min:4'
                
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
           // date_default_timezone_set('Asia/Kolkata');
            $booking_status = 'schedule';
            $schedule_later = $request->schedule_later;
            $time = strtotime($schedule_later); // timestamp from RFC 2822
            $schedules = date('Y-m-d H:i:s', $time); // 2014-06-30 10:30:00
            $user_id  = Auth::user()->id;
            $booking = new Bookings([
                        'user_id'             => $user_id,
                        'booking_status'      => $booking_status,
                        'schedule_later'      => $schedules
                        
                    ]);
            $booking->save();
            $infoArrayz = Addtocarts::where('user_id','=', $user_id)->get()->toArray();
           
                    foreach ($infoArrayz as $key => $value) {
                    $order = new Otherorderdatas([
                        'sub_category_id' => $value['sub_category_id'],
                        'sub_category_name' => $value['sub_category_name'],
                       // 'sub_category_amount' => $value['sub_category_amount'],
                        'user_id' => $user_id
                    ]);
                    $order->save();   
                    }
                    DB::table('addtocarts')->where('user_id', '=', $user_id)->delete();
                    return response()->json([
                        'message' => 'Scheduled Successfully.',
                        'status' => true,
                        'status_code' => 200
                    ]);
            }
    }
    public function getschedule(Request $request)
    {
        $infoArray = [];		
        $user_id  = Auth::user()->id; 
        $infoArray = Bookings::where([['booking_status','=', 'schedule'],['user_id', '=', $user_id]])->get()->toArray();
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

/*********jagan*********/
    public function providerBookinglist(Request $request)
    {
         // $user = User::find(auth()->user()->id);
          $user_id = auth()->user()->id;
          $user_data     = Extras::where([['provider_id','=',$user_id],[ 'active_status','=',1]])->get()->toArray();
          if(!empty($user_data))
          {
             $lat   = $user_data[0]['latitude'];
          $lng   = $user_data[0]['longitude']; 
        // $booking = Bookings::all();
        $haversine = "(6371 * acos(cos(radians(" . $lat . ")) 
                    * cos(radians(`latitude`)) 
                    * cos(radians(`longitude`) 
                    - radians(" . $lng . ")) 
                    + sin(radians(" . $lat . ")) 
                    * sin(radians(`latitude`))))";
        $radius = 15;
        $infoArray = Otherorderdatas::select('booking_id','booked_time','sub_category_id','sub_category_name','address','user_id','created_at')->selectRaw("{$haversine} AS distance")->whereRaw("{$haversine} < ?", [$radius])->where('flag','=',0)->get()->toArray();

       if(!empty($infoArray))   
        {
          $i = 0;
          foreach ($infoArray as $key => $value) 
          {
            $date1    = date("Y-m-d H:i:s");
            $date2    = $value['created_at'];
            $seconds  = strtotime($date1) - strtotime($date2);
            if($seconds < 121)
            {
              $second = 120 - $seconds;
            }
            else
            {
              $second = 0;
            }
            $mins     = round($seconds / 60);
            $request_check   = Request_details::where('request_id','=',$value['sub_category_id'])->where('booking_id','=',$value['booking_id'])->where('user_id','=',$user_id)->get()->toArray();
            if(empty($request_check) && $seconds < 121 && $seconds > -1)
            {
          
              $user_data     = User::select('fname','lname')->where('id','=',$value['user_id'])->get()->first();
              $sub_category_image     = Categorys::select('sub_category_image')->where('sub_category_id','=',$value['sub_category_id'])->get()->first();
              $infoArrays[$i]['booking_id']         = $value['booking_id'];
              $infoArrays[$i]['sub_category_id']    = $value['sub_category_id'];
              $infoArrays[$i]['sub_category_name']  = $value['sub_category_name'];
              $infoArrays[$i]['sub_category_image'] = $sub_category_image['sub_category_image'];
              $infoArrays[$i]['user_id']            = $value['user_id'];
              $infoArrays[$i]['user_name']          = $user_data['fname']." ".$user_data['lname'];
              $infoArrays[$i]['booked_time']        = $value['booked_time'];
              $infoArrays[$i]['fulladdress']        = $value['address'];
              $infoArrays[$i]['distance']           = round($value['distance']);
              $infoArrays[$i]['seconds']            = $second;
              $i++;
            }
          }

          if($i == 0)
          {
            $infoArrays = array();            
          }


        return response()->json([
            'message' =>  'List of User Request.',
            'status' => true,
            'status_code' => 200,
            'result' => $infoArrays
        ]); 
        }
         else
         {
            return response()->json([
                            'message' => 'Data Not Found.',
                            'result'=>$infoArray,
                            'status' => true,
                            'status_code' => 200
                        ]);
         }  
          }
          else
         {
            return response()->json([
                            'message' => 'Data Not Found.',
                            'result'  => array(),
                            'status' => true,
                            'status_code' => 200
                        ]);
         }  
    }

    public function user_request(Request $request)
    {
      $validator = Validator::make($request->all(), [
                'request_id'   => 'required',
                'booking_id'   => 'required',
                'user_request' => 'required'
                
            ]);
      if($validator->fails()) 
      {
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
        if($request->user_request == 0)
        {
          $request_check   = Request_details::where('request_id','=',$request->request_id)->where('booking_id','=',$request->booking_id)->where('request_flag','=',0)->get()->toArray();
          if(empty($request_check))
          {
            $request_otp = rand(100000,999999);
            $request_details = new Request_details([
              'request_id'  => $request->request_id,
              'user_id'     => $user_id,
              'booking_id'  => $request->booking_id,
              'request_otp' => (string)$request_otp
            ]);
            $request_details->save();
            Otherorderdatas::where('booking_id','=', $request->booking_id)->where('sub_category_id','=',$request->request_id)->update(['flag' => 1]); 
            $data = Otherorderdatas::where('booking_id','=', $request->booking_id)->where('sub_category_id','=',$request->request_id)->get()->first();
            $user_data = User::where('id','=',$data['user_id'])->get()->toArray();
            if(!empty($user_data))
            {
              if($user_data[0]['device_token'] != '' && $user_data[0]['device_type'] != '')
              {
                # code...
                $receiver_id = $data['user_id'];
                $device = array(
                          'is_apple'  => $user_data[0]['device_type'],
                          'endpoint'  => $user_data[0]['device_token']
                        );
                $msg = array(
                                'title'     => "Notification for Samarnas",
                                'message'   => "Provider Accept the your booking request",
                                'user_type' => "customer",
                                'type'      => "2"
                              );
                $notify = new Notification();
                $dd     = $notify->notification($device,$msg);

                $saveNotify   = new Notification([
                                            'sender_id'     => $user_id,
                                            'receiver_id'   => $receiver_id,
                                            'title'         => "request_accept",
                                            'message'       => json_encode($msg),
                                            'type'          => "request_accept",
                                            'status'        => 0
                          ]);
                          $saveNotify->save();
              }
            }
            return response()->json([
                'message'     =>  'Request Accept',
                'status'      => true,
                'status_code' => 200
            ]); 
          }
          else
          {
            return response()->json([
                'message'     =>  'Already other Provider Accept this booking',
                'status'      => false,
                'status_code' => 203
            ]); 
          }
        }
        else
        {
           $request_details = new Request_details([
              'request_id'  => $request->request_id,
              'user_id'     => $user_id,
              'booking_id'  => $request->booking_id,
              'request_flag'=> 1
            ]);
            $request_details->save();
            return response()->json([
                'message'     =>  'Request Cancelled',
                'status'      => true,
                'status_code' => 200
            ]);
        }  
      }
    }

    public function providerCancelRequest(Request $request)
    {
      $validator = Validator::make($request->all(), [
                'request_id'   => 'required',
                'booking_id'   => 'required'
                
            ]);
      if($validator->fails()) 
      {
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
        Request_details::where('request_id','=',$request->request_id)->where('booking_id','=',$request->booking_id)->where('user_id','=',$user_id)->update(['request_flag' => 1]);
        Otherorderdatas::where('booking_id','=', $request->booking_id)->where('sub_category_id','=',$request->request_id)->update(['flag' => 0]);
        $data   = Otherorderdatas::where('booking_id','=', $request->booking_id)
                            ->where('sub_category_id','=',$request->request_id)->get()->toArray(); 
        $createUser = $data[0]['user_id'];
        $latitude   = $data[0]['latitude'];
        $longitude  = $data[0]['longitude'];
        $haversine  = "(6371 * acos(cos(radians(" . $latitude . ")) 
                              * cos(radians(`latitude`)) 
                              * cos(radians(`longitude`) 
                              - radians(" . $longitude . ")) 
                              + sin(radians(" . $latitude . ")) 
                              * sin(radians(`latitude`))))";
                  $radius = 15;
        $infoArray = Extras::select('provider_id')->selectRaw("{$haversine} AS distance")->whereRaw("{$haversine} < ?", [$radius])->where('provider_id','!=',$user_id)->get()->toArray();
        $createUserData   = User::where('id','=',$createUser)->get()->toArray();
        $createUserName   = $createUserData[0]['fname']." ".$createUserData[0]['lname'];
        // $createUserDevice_token   = $createUserData[0]['device_token'];
        // $createUserDevice_type    = $createUserData[0]['device_type'];
        if(!empty($infoArray))
        {
          foreach ($infoArray as $key => $value) 
          {
            $users = User::where('id','=',$value['provider_id'])->get()->toArray();
            if(!empty($users))
            {
              if($users[0]['device_token'] != '' && $users[0]['device_type'] != '')
              {
                $receiver_id  = $value['provider_id'];
                # code...
                $device = array(
                          'is_apple'  => $users[0]['device_type'],
                          'endpoint'  => $users[0]['device_token']
                        );
                $msg = array(
                      'title'           => "Notification for Samarnas",
                      'message'         => "New Order Request from ".$createUserName,
                      'user_type'       => "provider",
                      'sub_category_id' => 0,
                      'type'            => "1"
                    );
                $notify = new Notification();
                $dd = $notify->notification($device,$msg);
                $saveNotify   = new Notification([
                                  'sender_id'     => $user_id,
                                  'receiver_id'   => $receiver_id,
                                  'title'         => "book_now",
                                  'message'       => json_encode($msg),
                                  'type'          => "book_now",
                                  'status'        => 0
                ]);
                $saveNotify->save();
              }
            }
          }
        }
        // $device = array(
        //                   'is_apple'  => $createUserDevice_type,
        //                   'endpoint'  => $createUserDevice_token
        //                 );
        //         $msg = array(
        //               'title'     => "Notification for Samarnas",
        //               'message'   => "Your Request has been cancelled by provider.We allowcate another provider for your service",
        //               'user_type' => "customer",
        //               'type'      => "4"
        //             );
        //         $notify = new Notification();
        //         $dd = $notify->notification($device,$msg);
        //         $saveNotify   = new Notification([
        //                           'sender_id'     => $user_id,
        //                           'receiver_id'   => $receiver_id,
        //                           'title'         => "cancel_provider",
        //                           'message'       => json_encode($msg),
        //                           'type'          => "cancel_provider",
        //                           'status'        => 0
        //         ]);
        //         $saveNotify->save();
        return response()->json([
                'message'     =>  'Successfully Request Cancelled',
                'status'      => true,
                'status_code' => 200
            ]);
      }
    }

    public function get_user_request_list(Request $request)
    {
      $user_id = auth()->user()->id;
      $requestDetails = new Request_details();
      $request_list   = $requestDetails->get_user_requestList($user_id);
      $request_list   = json_decode(json_encode($request_list),true);
      if(!empty($request_list))
      {
        foreach ($request_list as $key => $value) 
        {
          $request_list[$key]['profile_pic']  = "http://temp.pickzy.com/samarnas/storage/app/public".$value['profile_pic'];
          $checkStart   = Services_processes::where('booking_id','=',$value['booking_id'])
                                              ->where('sub_category_id','=',$value['request_id'])
                                              ->get()
                                              ->toArray();
          if(!empty($checkStart))
          {
            $request_list[$key]['start_status']   = true;
          }
          else
          {
            $request_list[$key]['start_status']   = false;
          }
        }
        return response()->json([
                'message'     =>  'Provider Request List',
                'request_list'=> $request_list,
                'status'      => true,
                'status_code' => 200
            ]);
      }
      else
      {
        $request_list   = array();
        return response()->json([
                'message'     =>  'No data found',
                'request_list'=> $request_list,
                'status'      => true,
                'status_code' => 200
            ]);
      }
    }

    public function requestdetailscount(Request $request)
    {
      $user_id = auth()->user()->id;
      $requestDetails = new Request_details();
      $request_list   = $requestDetails->get_user_requestList($user_id);
      $request_list   = json_decode(json_encode($request_list),true);
      if(!empty($request_list))
      {
        return response()->json([
                'message'           =>  'Provider Request count',
                'request_list_count'=> count($request_list),
                'status'            => true,
                'status_code'       => 200
            ]);
      }
      else
      {
        $request_list   = array();
        return response()->json([
                'message'           =>  'No data found',
                'request_list_count'=> 0,
                'status'            => true,
                'status_code'       => 200
            ]);
      }
    }

    public function get_customer_request_list(Request $request)
    {
      $user_id = auth()->user()->id;
      $requestDetails = new Request_details();
      $request_list   = $requestDetails->get_customer_requestList($user_id);
      $request_list   = json_decode(json_encode($request_list),true);
      if(!empty($request_list))
      {
        foreach ($request_list as $key => $value) 
        {
          $request_list[$key]['profile_pic']  = "http://temp.pickzy.com/samarnas/storage/app/public".$value['profile_pic'];
          $request_list[$key]['rating']  = "3";
           $checkStart   = Services_processes::where('booking_id','=',$value['booking_id'])
                                              ->where('sub_category_id','=',$value['request_id'])
                                              ->get()
                                              ->toArray();
          if(!empty($checkStart))
          {
            $request_list[$key]['start_status']   = true;
          }
          else
          {
            $request_list[$key]['start_status']   = false;
          }
          $language   = explode(',', $value['language']);
          foreach ($language as $key1 => $value1) 
          {
            $lang   = $requestDetails->getLanguage($value1);
            $lang   = json_decode(json_encode($lang),true);
            $langs[]= $lang[0]['language_name'];  
          }
          $request_list[$key]['language']  = implode(',', $langs);
          $langs    = array();
        }
        return response()->json([
                'message'     =>  'Customer Request List',
                'request_list'=> $request_list,
                'status'      => true,
                'status_code' => 200
            ]);
      }
      else
      {
        $request_list   = array();
        return response()->json([
                'message'     =>  'No data found',
                'request_list'=> $request_list,
                'status'      => true,
                'status_code' => 200
            ]);
      }
    }
}
