<?php

namespace samarnas\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Validator;
use Auth;
use DB;
use Bookings;
use Storage;
use Carbon\Carbon;
use samarnas\Extras;
use samarnas\ProviderDocument;
use samarnas\Request_details;
use samarnas\Services_processes;
use samarnas\User;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function togglestatus(Request $request)
    {
        $user_id = auth()->user()->id;
        Extras::where('provider_id',$user_id)->update(['active_status'=> $request->active_status]);
        return response()->json([
            'message' => 'Status updated successfully.',
            'status' => true,
            'status_code' => 200
        ]);
    }
    public function gettogglestatus(Request $request)
    {
        $user_id = auth()->user()->id;
        $infoArray = Extras::where('provider_id',$user_id)->get()->toArray();
       $rest = array("active_status" => (string) $infoArray[0]['active_status']);
         return response()->json([
            'message' => 'Provider Status',
            'status' => true,
            'status_code' => 200,
            'result' => $rest
           
        ]); 
    }
    public function newservicerequest(Request $request)
    {
        $user_id = auth()->user()->id;
        
        $infoArray = Extras::where('provider_id',$user_id)->get()->toArray();
       $rest = array("active_status" => (string) $infoArray[0]['active_status']);
         return response()->json([
            'message' => 'Provider Status',
            'status' => true,
            'status_code' => 200,
            'result' => $rest
           
        ]); 
    }
    public function uploaddoc(Request $request)
    {
        $user_id = auth()->user()->id;
        $document_type = $request->file('document_type');
        if($document_type !='experience_doc')
        {
            $value = $request->file('id_proof');
            $image = ltrim(Storage::put('public/id_proof', $value), 'public');
        }
        else
        {
            $value = $request->file('experience_doc');
            $image = ltrim(Storage::put('public/experience_doc', $value), 'public');
        }
        $infoArray = ProviderDocument::where([['document_id','=',$request->document_id],['provider_id','=',$user_id]])->update(['document_status'=> 'pending','document_url'=>$image]);
        
    }

    public function startProvider(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'user_id'       => 'required',
                'latitude'          => 'required',
                'longitude'         => 'required',
                'sub_category_id'   => 'required',
                'request_type'      => 'required',
                'booking_id'        => 'required'
                
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
            $provider_id = auth('api')->user()->id;
            if($request->request_type == 1)
            {

                $Services_processes = new Services_processes([
                            'provider_id'       => $provider_id,
                            'start_latitude'    => $request->latitude,
                            'start_longitude'   => $request->longitude,
                            'user_id'           => $request->user_id,
                            'booking_id'        => $request->booking_id,
                            'sub_category_id'   => $request->sub_category_id 
                        ]);
                $Services_processes->save();
                return response()->json([
                                    'message' => 'Provider Start the service',
                                    'status' => true,
                                    'status_code' => 200
                                ]); 
            }
            else
            {
                $Services_processes = Services_processes::where([['provider_id','=',$provider_id],['user_id','=',$request->user_id],['booking_id','=',$request->booking_id],['sub_category_id','=',$request->sub_category_id]])->update(['end_latitude'=> $request->latitude,'end_longitude'=>$request->longitude]);
                
                return response()->json([
                                    'message' => 'Provider End the service',
                                    'status' => true,
                                    'status_code' => 200
                                ]);
            }
        }
    }

    public function documentList(Request $request)
    {
        $provider_id    = auth('api')->user()->id;
        $userData       = User::where('id','=',$provider_id)->get()->toArray();
        if($userData[0]['user_type'] == 1)
        {
            foreach ($userData as $key => $value) 
            {
                $documentData[$key]['document_url']                 = "http://temp.pickzy.com/samarnas/storage/app/public".$value['id_proof'];
                $documentData[$key]['document_status']              = $value['id_proof_status_for_user'];
                $documentData[$key]['id_proof_reject_for_prov']     = $value['id_proof_reject_for_user'];
                $documentData[$key]['exp_proof_reject_for_prov']    = "";
                $documentData[$key]['document_type']                = "id_proof";
                $documentData[$key]['document_id']                  = $provider_id;
            }
        }
        else if($userData[0]['user_type'] == 3 || $userData[0]['user_type'] == 4)
        {
            $data = ProviderDocument::where('provider_id','=',$provider_id)->get()->toArray(); 
            $i    = 0;
            $j    = 0;
            $k    = 0;
            foreach ($data as $key => $value) 
            {
                if($value['document_type'] == 'id_proof')
                {
                    $documentData[$i]['document_url']             = "http://temp.pickzy.com/samarnas/storage/app/public".$value['document_url'];
                    $documentData[$i]['document_status']          = $value['document_status'];
                    $documentData[$i]['id_proof_reject_for_prov'] = $value['id_proof_reject_for_prov'];
                    $documentData[$i]['document_type']            = $value['document_type'];
                    $documentData[$i]['exp_proof_reject_for_prov']= $value['exp_proof_reject_for_prov'];
                    $documentData[$i]['document_id']              = $value['document_id'];
                    $i++;
                }
                if($value['document_type'] == 'experience_doc')
                {
                    $documentData1[$j]['document_url']             = "http://temp.pickzy.com/samarnas/storage/app/public".$value['document_url'];
                    $documentData1[$j]['document_status']          = $value['document_status'];
                    $documentData1[$j]['id_proof_reject_for_prov'] = $value['id_proof_reject_for_prov'];
                    $documentData1[$j]['document_type']            = $value['document_type'];
                    $documentData1[$j]['exp_proof_reject_for_prov']= $value['exp_proof_reject_for_prov'];
                    $documentData1[$j]['document_id']              = $value['document_id'];
                    $j++;
                }
            }
        }
        if(!empty($documentData) || !empty($documentData1))
        {
            if($userData[0]['user_type'] == 3 || $userData[0]['user_type'] == 4)
            {

                return response()->json([
                                            'message'       => 'Document details',
                                            'documentData'  => array(
                                                                        array(
                                                                                'document_type'     => 'id_proof',
                                                                                'data'            => $documentData

                                                                            ),
                                                                        array(
                                                                                'document_type'     => 'experience_doc',
                                                                                'data'           => $documentData1
                                                                            )
                                                                    ),
                                            'status'        => true,
                                            'status_code'   => 200
                                        ]);
            }
            else if($userData[0]['user_type'] == 1)
            {
               // $documentData1 = array();
                 return response()->json([
                                            'message'       => 'Document details',
                                            'documentData'  => array(
                                                                        array(
                                                                                'document_type'     => 'id_proof',
                                                                                'data'            => $documentData

                                                                            )
                                                                    ),
                                            'status'        => true,
                                            'status_code'   => 200
                                        ]);
            }
        }
        else
        {
            return response()->json([
                                        'message'       => 'No data found',
                                        'documentData'  => $documentData,
                                        'status'        => true,
                                        'status_code'   => 200
                                    ]);
        }
    }

    public function documentUpload(Request $request)
    {
       $validator = Validator::make($request->all(), [
                'document_id'       => 'required',
                'user_type'         => 'required',
                'document_type'     => 'required',
                'document_file'     => 'required|file'
                
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
            $provider_id    = auth('api')->user()->id;
            // print_r($provider_id);
            // exit();
            if($request->user_type == '1')
            {
                $value = $request->file('document_file');
                $image = ltrim(Storage::put('public/id_proof', $value), 'public');
                $infoArray = User::where('id','=',$request->document_id)->update(['id_proof_status_for_user'=> 'pending','id_proof'=>$image]);
            }
            else if($request->user_type == '3' || $request->user_type == '4')
            {              
                if($request->document_type !='experience_doc')
                {
                    $value = $request->file('document_file');
                    $image = ltrim(Storage::put('public/id_proof', $value), 'public');
                }
                else
                {
                    $value = $request->file('document_file');
                    $image = ltrim(Storage::put('public/experience_doc', $value), 'public');
                }
                $infoArray = ProviderDocument::where('document_id','=',$request->document_id)->update(['document_status'=> 'pending','document_url'=>$image,'document_type'=>$request->document_type]);
            }

            return response()->json([
                                        'message'       => 'Document Updated',
                                        'status'        => true,
                                        'status_code'   => 200
                                    ]);
            
        }
    }

    public function providerWorkHistory(Request $request)
    {
        $provider_id    = auth('api')->user()->id;
       // dd($provider_id);
        $data           = Request_details::join('otherorderdatas as ood','ood.booking_id','=','request_details.booking_id')
                                            ->join('users as us','us.id','=','ood.user_id')
                                            ->join('orderdetails as od','od.booking_id','=','ood.booking_id')
                                            ->join('categorys as cs','cs.sub_category_id','=','request_details.request_id')
                                            ->select('us.id as customer_id','us.fname','us.lname','us.profile_pic','ood.booking_id','request_details.request_id','cs.sub_category_name','ood.flag','request_details.created_at','cs.sub_category_time_limit as service_time','ood.address as customer_address','ood.sub_category_amount as invoice_amount','od.payment_id')
                                            ->where('ood.flag','=',3)
                                            ->where('request_details.user_id','=',$provider_id)
                                            ->orWhere('ood.flag','=',6)
                                            ->get()
                                            ->groupBy(function($d) {
                                                 return Carbon::parse($d->created_at)->format('m');
                                             })
                                            ->toArray();
        if(!empty($data))
        {
            foreach ($data as $key => $value) 
        {
            foreach ($data[$key] as $key1 => $value1) 
            {
                $data[$key][$key1]['profile_pic']    = "http://temp.pickzy.com/samarnas/storage/app/public".$value1['profile_pic'];
            }
        }
        $x = array_keys($data);
        sort($x);
        
        $i = 0;
        foreach ($x as $key => $value) 
        {
            if($value == 01)
            {
                $date = 10;
            }
            else if($value == 02)
            {
                $date = 9;
            }
            else if($value == 03)
            {
                $date = 8;
            }
            else if($value == 04)
            {
                $date = 7;
            }
            else if($value == 05)
            {
                $date = 6;
            }
            else if($value == 06)
            {
                $date = 5;
            }
            else if($value == 07)
            {
                $date = 4;
            }
            else if((string)$value == '08')
            {
                $date = 3;
            }
            else if((string)$value == '09')
            {
                $date = 2;
            }
             else if($value == 10)
            {
                $date = 1;
            }
            else if($value == 11)
            {
                $date = 0;
            }
            else if($value == 12)
            {
                $date = 11;
            }
            
            $firstDayofPreviousMonth    = Carbon::now()->startOfMonth()->subMonth($date)->toDateString();
            $lastDayofPreviousMonth     = Carbon::now()->subMonth($date)->endOfMonth()->toDateString();
            $data1[$i]['from_data']     = $firstDayofPreviousMonth;
            $data1[$i]['end_data']      = $lastDayofPreviousMonth;
            $data1[$i]['child_data']    = $data[$value];
            $i++;
            
        }

       return response()->json([
                                        'message'       => 'Document Updated',
                                        'data'          => $data1,
                                        'status'        => true,
                                        'status_code'   => 200
                                    ]);
        }
        else
        {
            $data1 = array();
            return response()->json([
                                        'message'       => 'No data Found',
                                        'data'          => $data1,
                                        'status'        => true,
                                        'status_code'   => 200
                                    ]);
        }
        

    }
    
}
