<?php
namespace samarnas\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

//use Auth;
use DB;
use samarnas\User;
use samarnas\Extras;
use samarnas\Admin;
use samarnas\gender_service;
use samarnas\Services;
use samarnas\Categorys;
use samarnas\Addtocarts;
use samarnas\ProviderServices;
use samarnas\ProviderSubcategory;
use samarnas\Gender_types;
use samarnas\Notification;
use samarnas\Request_details;
use samarnas\Otherorderdatas;
use samarnas\Price;
use samarnas\Rating;
use Storage;

class NotificationController extends Controller
{
  public function notificationList(Request $request)
  {
    $userId       = auth('api')->user()->id;
    $notificationData   = Notification::select('notifications.id','notifications.sender_id','notifications.receiver_id','notifications.title','notifications.message','notifications.type','notifications.status','notifications.created_at','us.profile_pic','us.fname','us.lname')->join('users as us','us.id','=','notifications.sender_id')->where('notifications.receiver_id','=',$userId)->get()->toArray();
    foreach ($notificationData as $key => $value) 
    {
      $notificationData[$key]['message']  = json_decode($value['message'],true);
      $notificationData[$key]['profile_pic']  = "http://temp.pickzy.com/samarnas/storage/app/public".$value['profile_pic'];
    }
    if(!empty($notificationData))
    {
      return response()->json([
                                        'message'           => "Notification List",
                                        'notificationData'  => $notificationData,
                                        'status'            => true,
                                        'status_code'       => 200
                                    ]);
    }
    else
    {
      return response()->json([
                                        'message'           => "No data found",
                                        'notificationData'  => $notificationData,
                                        'status'            => true,
                                        'status_code'       => 200
                                    ]);
    }
  }

  public function notificationStatusUpdate(Request $request)
  {
    $validator = Validator::make($request->all(), [
                'notification_id'   => 'required'
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
      $updateStatus = Notification::where('id','=',$request->notification_id)->update(['status' => 1]);
      return response()->json([
                'message'     => "Notification Status Updated",
                'status'      => true,
                'status_code' => 200
            ]);
    }
  }

  public function notificationCount(Request $request)
  {
    $userId       = auth('api')->user()->id;
    $notificationData   = Notification::where('notifications.receiver_id','=',$userId)->where('status','=',0)->get();
    $notificationDataCount = $notificationData->count();
    if(!empty($notificationData))
    {
      return response()->json([
                'message'     => "Notification Count",
                'notification_count' => $notificationDataCount,
                'status'      => true,
                'status_code' => 200
            ]);
    }
    else
    {
      return response()->json([
                'message'     => "Notification Count",
                'notification_count' => 0,
                'status'      => true,
                'status_code' => 200
            ]);
    }
  }
}

