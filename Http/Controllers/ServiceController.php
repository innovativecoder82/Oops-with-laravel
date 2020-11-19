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

class ServiceController extends Controller
{
  public function index()
    {
         $services = \DB::table('categorys')
                      ->join('services', 'services.service_id','=','categorys.service_id')
                      ->select('services.service_id as serve_id','categorys.*','services.service_name')
                      ->orderBy('service_id','asc')
                      ->get();
           $rests = json_decode($services, true);
           $services = Services::all()->toArray();
           $gender = Gender_types::all()->toArray();
           return view('manage_service', compact('services','rests','gender'));
    }

    // to show gender name in add services
       
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   
    public function save(Request $request)
    {       
        if($request->file('image'))
           {
                $this->validate($request, [
                    'image'             =>      'required|file|mimes:jpeg,jpg,png,gif|required|max:10000'
                ]);
               $image = ltrim(Storage::put('public/service_image', $request->file('image')), 'public'); 
           }else{
               $image = ""; 
           }
           $sername = $request->service_name;
        $services = new Services([
            'service_name'          =>  $sername,
            'image'                 =>  "http://temp.pickzy.com/samarnas/storage/app/public".$image,
            'gender_id'             => json_encode($request->gentype)
            
        ]);

        $services->save();
        
        $serve = Services::where ('service_name',$sername)->get();
        foreach ($serve as $key => $val) {  }
        //print_r($request->gentype); exit();
            $gentype = $request->gentype;
        
        foreach ($gentype as $key ) {
            # code...
       $genservice = new gender_service([

            'service_id'       => $val->service_id,
            'gender_id'        => $key 
        ]); // print_r($genservice); exit();
   
        $genservice->save(); 
      }
        return back()->with('success', 'Service Inserted');
    
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
   
        $id = $request->input('service_id'); 
        $services = Services::where('service_id',$id)->get()->toArray();

        foreach ($services as $key => $serve_val) {}
        $gendertype = gender_types::all()->toArray(); 
      
        $gen = \DB::table('gender_types')
        ->join ('gender_services' , 'gender_services.gender_id', '=', 'gender_types.type_id')
        ->select('gender_types.type_id','gender_types.gender_type_name','gender_services.*')
        ->where('gender_services.service_id','=',$serve_val['service_id'])
        ->get();
         $rests = json_decode($gen, true);
           return view('EditService', compact('serve_val','rests','gendertype'));
    }

    public function update(Request $request)
    { 
      //print_r($request->id); exit();
      
        
      if($request->file('image'))
           {
               $this->validate($request, [
                    'image'             =>      'required|file|mimes:jpeg,jpg,png,gif|required|max:10000'
                ]);
                $image = ltrim(Storage::put('public/service_image', $request->file('image')), 'public');  
                $image = "http://temp.pickzy.com/samarnas/storage/app/public".$image;
           }else{
               $image = $request->get('old_img'); 
           }
        Services::where('service_id',$request->id)->update(['service_name'=> $request->service_name,'image'=>$image]);

        DB::table('gender_services')
            ->where('service_id','=',$request->id)
            ->delete();

        $gentype = $request->gentype;
       //print_r($gentype); exit();
        foreach ($gentype as $key => $value) {
            //echo $value; 
        $genservice = new gender_service([
            'service_id'       => $request->id,
            'gender_id'        => $value
        ]);
   
        $genservice->save();
            }
        return back()->with('success', 'Service Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
      $id = $request->input('service_id'); //print_r($id); exit();
      Categorys::where('sub_category_id', $id)->delete();
        DB::table('services')
            ->where('service_id','=',$id)
            ->delete();
        return back()->with('success', 'Service Deleted');
    }

    public function view(Request $request)
    {        
        // print_r($request->gentype); exit();
        $id = $request->input('service_id'); 

        $services = Services::where('service_id',$id)->get()->toArray();

        foreach ($services as $key => $serve_val) {}
        $gendertype = gender_types::all()->toArray(); 

        $gen = \DB::table('gender_types')
        ->join ('gender_services' , 'gender_services.gender_id', '=', 'gender_types.type_id')
        ->select('gender_types.type_id','gender_types.gender_type_name','gender_services.*')
        ->where('gender_services.service_id','=',$serve_val['service_id'])
        ->get();
         $rests = json_decode($gen, true);
         return view('service_view', compact('serve_val','rests','gendertype'));
    }
    public function getallservices(Request $request)
    {
        $infoArray = [];
        $user_id = $request->user_id;
        $getuserid = User::where('id','=', $user_id)->get();
        //print_r($getuserid);
        $infoArray = Services::where('gender','=', $getuserid[0]['gender'])->get()->toArray();
       // print_r($infoArray); exit();
        if(!empty($infoArray))
        {
        	foreach ($infoArray as $key => $value) 
        {
           $infoArrays[$key]['service_id'] = $value['service_id'];
           $infoArrays[$key]['service_name'] = $value['service_name'];
           $infoArrays[$key]['image'] = $value['image'];
           /*$infoArrays[$key]['category_image'] = $value['category_image'];
           $infoArrays[$key]['type'] = 'category';*/
        }

        return response()->json([
            'message' => trans('messages.getservices'),
            'status' => true,
            'status_code' => 200,
            'result' => $infoArrays
        ]); 
        }
        else{
            return response()->json([
            'message' => trans('messages.getservices'),
            'status' => true,
            'status_code' => 200,
            'result' => $infoArray
        ]);       
        
        } 
        
    } 
    public function getservicebygender(Request $request)
    {
        $infoArray = [];
        $gend = $request->gender_type;
        $lat = $request->latitude;
        $lng = $request->longitude;
        $circle_radius = 3959;
        $max_distance = 20;
       /* $infoArray = DB::select('SELECT * FROM
                    (SELECT provider_services.service_id as service_id, (' . $circle_radius . ' * acos(cos(radians(' . $lat . ')) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(' . $lng . ')) +
                    sin(radians(' . $lat . ')) * sin(radians(latitude))))
                    AS distance 
                    FROM extras INNER JOIN provider_services ON extras.provider_id = provider_services.provider_user_id WHERE `provider_services` .`gender_type_id` = '.$gend.' ) AS distances WHERE distance < ' . $max_distance . '
                ORDER BY distance
                LIMIT 20;')->distinct()->get(['service_id']);*/


$infoArray = Extras::select(DB::raw('extras.provider_id as provider_id,provider_services.service_id as service_id, ( 6367 * acos( cos( radians('.$lat.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$lng.') ) + sin( radians('.$lat.') ) * sin( radians( latitude ) ) ) ) AS distance'))->join('provider_services', 'extras.provider_id', '=', 'provider_services.provider_user_id')
    ->having('distance', '<', 20)
    ->orderBy('distance')
    ->get();
$infoArray = $infoArray->unique('service_id');
$infoArray = array_slice($infoArray->values()->all(), 0, 10, true);

       
//print_r($infoArray); exit();
        if(!empty($infoArray))
        {
          $i = 0;
        	foreach($infoArray as $key =>$value)
        {
           //$infoArrays[$key]['service_id'] = $value->service_id;
           $servicename = Services::select('services.service_id','services.service_name','services.image')->join('gender_services AS gs','gs.service_id','=','services.service_id')->where('services.service_id','=', $value->service_id)->where('gs.gender_id','=', $gend)->get()->toArray();
           // print_r($servicename);
           // exit();
           if(!empty($servicename))
           {
            
           $infoArrays[$i]['service_id'] = $servicename[0]['service_id'];
           $infoArrays[$i]['service_name'] = $servicename[0]['service_name'];
           $infoArrays[$i]['image'] = $servicename[0]['image'];
           $i++;
           }
           // $infoArrays[$key]['image'] = "http://103.249.82.10:8000/samarnas/storage/app/public/services/".$servicename[0]['image'];
           /*$infoArrays[$key]['category_image'] = $value['category_image'];
           $infoArrays[$key]['type'] = 'category';*/
        }

        if($i == 0)
        {
          $infoArrays = array();
        }

        return response()->json([
            'message' => trans('messages.allservice'),
            'status' => true,
            'status_code' => 200,
            'result' => $infoArrays
        ]); 
        }
        else{
            return response()->json([
            'message' => trans('messages.allservice'),
            'status' => true,
            'status_code' => 200,
            'result' => $infoArray
        ]);       
        
        } 
        
    } 
    public function getcategorybyservice(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'service_id'   => 'required|min:1',
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
        $infoArray = [];
        $service_id = $request->service_id;
        $user_id = $request->user_id;
        $infoArray = Categorys::where('service_id','=', $service_id)->get()->toArray();
       // print_r($infoArray);
        $i = 0;
        if(!empty($infoArray))
        {
          foreach ($infoArray as $key => $value) 
          {
           $getcarts = Addtocarts::where([['sub_category_id','=', $value['sub_category_id']],['user_id', '=', $user_id]])->get()->toArray();
           $amountlist = Price::where([['service_id','=', $value['sub_category_id']],['country_id', '=', 1]])->get()->toArray();
         // print_r($getcarts);  exit();
         //echo $getcarts['sub_category_id'];
          //$getcount = count($getcarts[0]['sub_category_id']); 
           if(!empty($amountlist))
           {

            $infoArrays[$key]['sub_category_id'] = $value['sub_category_id'];
            $infoArrays[$key]['sub_category_name'] = $value['sub_category_name'];
            $infoArrays[$key]['sub_category_amount'] = (string)$amountlist[0]['amount'];
            $infoArrays[$key]['sub_category_image'] = $value['sub_category_image'];
            $infoArrays[$key]['sub_category_time_limit'] = $value['sub_category_time_limit'];
            if(isset($getcarts[0]['sub_category_id']))
            {
              $infoArrays[$key]['item_added_cart'] = 1;
            }
            else
            {
              $infoArrays[$key]['item_added_cart'] = 0;
            }
            $i++;
           }
          
          
          }

          if($i == 0)
          {
            $infoArrays = array();
          }

        return response()->json([
            'message' => trans('messages.category'),
            'status' => true,
            'status_code' => 200,
            'result' => $infoArrays
        ]); 
        }
        else{
            return response()->json([
            'message' => trans('messages.category'),
            'status' => true,
            'status_code' => 200,
            'result' => $infoArray
        ]); 
        } 
        }
    } 

    
    public function getServiceList(Request $request)
    {
      $id = auth('api')->user()->id;
      //dd($id);
      $services = Services::where('service_id','!=','')->get()->toArray();
      foreach ($services as $key => $value) 
      {
        $checkData  = Providerservices::where('service_id','=',$value['service_id'])->where('provider_user_id','=',$id)->get()->toArray();
        if(!empty($checkData))
        {
          $services[$key]['isChecked']  = true;
        }
        else
        {
          $services[$key]['isChecked']  = false;
        }
      }
      if(!empty($services))
      {
        return response()->json([
                          'message'     => "List of Services.",
                          'services'    => $services,
                          'status'      => true,
                          'status_code' => 200
                          ]);
      }
      else
      {
        return response()->json([
                        'message'     => "No data Found.",
                        'status'      => false,
                        'status_code' => 204
                        ]);
      }
    }

    public function submitService(Request $request)
    {
      $validator = Validator::make($request->all(), [
                'category_list'   => 'required'
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
        $intro = explode(',', $request->category_list);
        $id = auth('api')->user()->id; 
        
        $delete = Providerservices::where('provider_user_id','=',$id)->delete();
        
        foreach ($intro as $key => $value) 
        {
          $employee = new Providerservices([
                                          'service_id'        => $value,
                                          'provider_user_id'  => $id,
                                          'gender_type_id'    => '1'
                                          ]);
          $employee->save();
        }
        return response()->json([
                          'message'     => "Services inserted successfully",
                          'status'      => true,
                          'status_code' => 200
                          ]);
      }
    }

    public function getUserSubService(Request $request)
    {
      $id = auth('api')->user()->id;
      $providerServices   = new ProviderServices();
      $services           = $providerServices->getSubCategory($id);
      if(!empty($services))
      {
        return response()->json([
                          'message'     => "List of Services based Categorys.",
                          'services'    => $services,
                          'status'      => true,
                          'status_code' => 200
                          ]);
      }
      else
      {
        return response()->json([
                        'message'     => "No data Found.",
                        'status'      => false,
                        'status_code' => 204
                        ]);
      }
    }

    public function submitUserSubService(Request $request)
    {
      // $subServiceList = $request->subServiceList;
      // return response()->json([
      //           'message' => $request->subServiceList,
      //           'status' => false,
      //           'status_code' => 422
      //       ]);
      $validator = Validator::make($request->all(), [
                'subServiceList'   => 'required'
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
        // $subServiceList = json_decode($request->subServiceList,true);
        $subServiceList = $request->subServiceList;
        $id = auth('api')->user()->id;
        ProviderSubcategory::where('provider_id','=',$id)->delete();
        foreach ($subServiceList as $key => $value) 
        {
          $ProviderSubcategory = new ProviderSubcategory([
                                          'sub_category_id' => $value,
                                          'provider_id'     => $id
                                          ]);
          $ProviderSubcategory->save();
        }
        return response()->json([
                          'message'     => "Category inserted successfully",
                          'status'      => true,
                          'status_code' => 200
                          ]);
      }
    }

    public function notification()
    {
      $device = array(
                      'is_apple'  => "true",
                      'endpoint'  => "54C11B7A22EBD9C4DE55DC2B06B0C8C8B3F5FB6AD716D937F6F783519CE716EF"
                    );
      $msg = array(
                  'title' =>"hi Andriod",
                  'message' => "Happy Morning"
      );
        $notify = new Notification();
        $dd = $notify->notification($device,$msg);
        return $dd;
    }

    public function otpVerification(Request $request)
    {
      $validator = Validator::make($request->all(), [
                'user_id'         => 'required',
                'booking_id'      => 'required',
                'sub_category_id' => 'required',
                'otp'             => 'required'
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
        $userId = auth('api')->user()->id;

        $data = Request_details::leftjoin('otherorderdatas as od','od.booking_id','=','request_details.booking_id')
                                ->select('request_details.request_otp','request_details.id')
                                ->where([['request_details.booking_id','=',$request->booking_id],
                                          ['request_details.request_id','=',$request->sub_category_id],
                                          ['od.user_id','=',$request->user_id],
                                          ['request_details.user_id','=',$userId],
                                          ['request_details.request_otp','=',$request->otp]
                                        ])
                                ->get()
                                ->toArray();
       
        if(!empty($data))
        {
          $data = Request_details::where('id','=',$data[0]['id'])
                                ->update(['request_details.otp_verification'=> "verified"]);
            return response()->json([
                                        'message' => "successfully OTP verified",
                                        'otp_status' => true,
                                        'status' => true,
                                        'status_code' => 200
                                    ]);
        }
        else
        {
            return response()->json([
                                        'message' => "Invalid Request",
                                        'otp_status' => false,
                                        'status' => false,
                                        'status_code' => 422
                                    ]);
        }
      }
    }

    public function serviceUpdate(Request $request)
    {
      $validator = Validator::make($request->all(), [
                'user_id'         => 'required',
                'booking_id'      => 'required',
                'sub_category_id' => 'required',
                'type'            => 'required'
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
        $userId = auth('api')->user()->id;
         //dd($userId);
        $data = Request_details::leftjoin('otherorderdatas as od','od.booking_id','=','request_details.booking_id')
                                ->leftjoin('users as us','us.id','=','od.user_id')
                                ->select('od.other_id','us.device_token','us.device_type','us.id')
                                ->where([['request_details.booking_id','=',$request->booking_id],
                                          ['request_details.request_id','=',$request->sub_category_id],
                                          ['od.user_id','=',$request->user_id],
                                          ['request_details.user_id','=',$userId]
                                        ]);

        if($request->type == 1)
        {
          $data = $data->where('od.flag','=',1)->get()->toArray();
        }
        else if($request->type == 2)
        {
          $data = $data->where('od.flag','=',2)->get()->toArray();
        }
                                
  
        if(!empty($data))
        {
          if($request->type == 1)
          {
            $data = Otherorderdatas::where('other_id','=',$data[0]['other_id'])
                                  ->update(['flag'=> 2]);
            return response()->json([
                                          'message'     => "Successfully Service started.",
                                          'status'      => true,
                                          'status_code' => 200
                                      ]);
          }
          else if($request->type == 2)
          {
            $device = array(
                      'is_apple'  => $data[0]['device_type'],
                      'endpoint'  => $data[0]['device_token']
                    );

            $receiver_id  = $data[0]['id'];
             $data = Otherorderdatas::where('other_id','=',$data[0]['other_id'])
                                   ->update(['flag'=> 3]);
  
            $providerData     = User::select('fname','lname')->where('id','=', $userId)->get()->first();
            $subcategoryData  = Categorys::select('sub_category_name')->where('sub_category_id','=', $request->sub_category_id)->get()->first();
            $msg    = array();
            $saveNotify   = new Notification([
                              'sender_id'     => $userId,
                              'receiver_id'   => $receiver_id,
                              'title'         => "complete",
                              'message'       => json_encode($msg),
                              'type'          => "complete",
                              'status'        => 0
            ]);
            $saveNotify->save();
            $notificationId = $saveNotify['id'];
            $msg = array(
                  'title'             => "Notification for Samarnas",
                  'message'           => "Successfully Your request Completed.",
                  'user_type'         => "customer",
                  'type'              => "3",
                  'booking_id'        => $request->booking_id,
                  'sub_category_id'   => $request->sub_category_id,
                  'sub_category_name' => $subcategoryData->sub_category_name,
                  'provider_name'     => $providerData->fname." ".$providerData->lname,
                  'provider_id'       => $userId,
                  'notification_id'   => $saveNotify['id']
                );
            $notify = new Notification();
            $dd = $notify->notification($device,$msg);
            Notification::where('id','=',$notificationId)->update(['message' => json_encode($msg)]);


            //dd($saveNotify['id']);
            return response()->json([
                                          'message'     => "Successfully Service Ended.",
                                          'status'      => true,
                                          'status_code' => 200
                                      ]);
          }
        }
        else
        {
            return response()->json([
                                        'message' => "Invalid Request",
                                        'status' => false,
                                        'status_code' => 422
                                    ]);
        }
      }
    }

    public function customerCompleteList(Request $request)
    {
      
      $userId = auth('api')->user()->id;
      // dd($userId);
      $data = Otherorderdatas::join('request_details as rd','rd.booking_id','=','otherorderdatas.booking_id')
                              ->join('users as us','us.id','=','rd.user_id')
                              ->join('categorys as cs','cs.sub_category_id','=','rd.request_id')
                              ->where([['otherorderdatas.user_id','=',$userId],['otherorderdatas.flag','=',3]])
                              ->select('us.fname','us.lname','us.id as provider_id','us.profile_pic','cs.sub_category_name','otherorderdatas.created_at')
                              ->get()
                              ->toArray();
      if(!empty($data))
      {
        return response()->json([
                                        'message'     => "Show Customer History",
                                        'status'      => true,
                                        'data'      => $data,
                                        'status_code' => 200
                                    ]);
      }
      else
      {
        return response()->json([
                                        'message'     => "No data found",
                                        'status'      => false,
                                        'data'      => $data,
                                        'status_code' => 204
                                    ]);
      }      
    }

    public function providerRating(Request $request)
    {
      $validator = Validator::make($request->all(), [
                'provider_id'     => 'required',
                'booking_id'      => 'required',
                'sub_category_id' => 'required',
                'quality_of_work' => 'required',
                'professionalism' => 'required',
                'value_of_money'  => 'required'
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
        $userId       = auth('api')->user()->id;
        $ratingData   = new Rating([
                              'provider_id'     => $request->provider_id,
                              'comment'         => $request->comment,
                              'user_id'         => $userId,
                              'quality_of_work' => $request->quality_of_work,
                              'professionalism' => $request->professionalism,
                              'value_of_money'  => $request->value_of_money,
                              'booking_id'      => $request->booking_id,
                              'request_id'      => $request->sub_category_id
            ]);
        $ratingData->save();
        if(!empty($ratingData))
        {
           return response()->json([
                                        'message'     => "Successfully saved rating",
                                        'status'      => true,
                                        'status_code' => 200
                                    ]);
        }
        else
        {
          return response()->json([
                                        'message'     => "Invalid formate",
                                        'status'      => false,
                                        'status_code' => 422
                                    ]);
        }
      }    
    }
}

