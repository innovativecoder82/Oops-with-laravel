<?php

namespace samarnas\Http\Controllers;
use Laravel\Passport\HasApiTokens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use samarnas\Notifications\SignupActivate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use samarnas\User;
use samarnas\Language;
use samarnas\Employees;
use samarnas\Locations;
use samarnas\ProviderSubcategory;
use samarnas\Extras;
use samarnas\Country;
use Mail;
use samarnas\ProviderDocument;

class AuthController extends Controller
{
    /**
     * Handles Registration Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function language(Request $request)
    {
            $language = Language::select('language_id','language_name')->where('language_id','!=','')->get();
            return response()->json([
                        'message' => "List of Language.",
                        'language' => $language,
                        'status' => true,
                        'status_code' => 200
                    ]);

    }

    public function activeStatus(Request $request)
    {
        Extras::where('provider_id','=',auth('api')->user()->id)->update(array('active_status' => $request->active_status));
        return response()->json([
                        'message' => "User status changed",
                        'status' => true,
                        'status_code' => 200
                    ]);
    }

    public function country(Request $request)
    {
            $country = Country::select('country_id','country_name','country_code')->where('country_id','!=','')->get();
            return response()->json([
                        'message' => "List of Country.",
                        'country' => $country,
                        'status' => true,
                        'status_code' => 200
                    ]);

    }
    public function updateaddress(Request $request)
    {
        $address    = $request->address.','.$request->city.','.$request->state.','.$request->zip_code;
        $latLong    = $this->getLatLong($address);
        $latitude   = $latLong['latitude'];
        $longitude  = $latLong['longitude'];
        Extras::where('provider_id','=',auth('api')->user()->id)->update(array(
                            'address' => $request->address,
                            'city' => $request->city,
                            'state' => $request->state,
                            'country_name' => $request->country_name,
                            'zip_code' => $request->zip_code,
                            'longitude' => $longitude,
                            'latitude' => $latitude
                        ));
        return response()->json([
                        'message' => "Provider Address Updated Successfully",
                        'status' => true,
                        'status_code' => 200
                    ]);
    }
    public function getprovideraddress(Request $request)
    {
            $provider = Extras::select('address','city','state','country_name','zip_code')->where('provider_id','=',auth('api')->user()->id)->get()->first();
            return response()->json([
                        'message' => "Provider Address Details.",
                        'result' => $provider,
                        'status' => true,
                        'status_code' => 200
                    ]);

    }


    public function register(Request $request)
    {
        $user_type = $request->user_type;
        if($user_type == 1)            
        {
             $userz = 'normal';
        }
        else if($user_type == 2) 
        {
             $userz = 'business';
        }
        else
        {
             return response()->json([
                        'message' => "Invalid user type.",
                        'status' => false,
                        'status_code' => 422
                    ]);
        }
        if($userz == 'normal'){
            $validator = Validator::make($request->all(), [
                'fname'   => 'required|min:3',
                'lname'   => 'required|min:1',
                'email'   => 'required|email|unique:users',
                'mobile'  => 'required|string|unique:users',
                'dob'     => 'required|string',
                'gender'  => 'required|string',
                'user_type' => 'required|string',
                'id_proof'  => 'required|file',
                'profile_pic'   => 'required|file',
                'password'  => 'required|string',
                
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
                $user = User::where('email',$request->email)->first();
            
                   // $id_proofs = Input::file('id_proof');
                    if($request->file('id_proof'))
                    {
                        $id_proof = ltrim(Storage::put('public/id_proof', $request->file('id_proof')), 'public'); 
                    }else{
                        $id_proof = ""; 
                    }
                    if($request->file('profile_pic'))
                    {
                        $profile_pic = ltrim(Storage::put('public/profile_pic', $request->file('profile_pic')), 'public'); 
                    }else{
                        $profile_pic = ""; 
                    }
                    $gens = $request->gender;
                    if($gens == 1)
                    {
                        $gender = 'Male';
                    }
                    else if($gens == 2)
                    {
                        $gender = 'Female';
                    }
                    else
                    {
                        return response()->json([
                        'message' => 'Invalid gender type',
                        'status' => false,
                        'status_code' => 422
                    ]);
                    }
                    $user = new User([
                        'fname'             => $request->fname,
                        'lname'             => $request->lname,
                        'email'             => $request->email,
                        'mobile'            => $request->mobile,
                        'password'          => Hash::make(rtrim($request->password)),
                        'dob'               => $request->dob,
                        'gender'            => $request->gender,
                        'user_type'         => $request->user_type,
                        'device_token'      => $request->device_token,
                        'id_proof'          => $id_proof,
                        'profile_pic'       => $profile_pic,
                        'activation_token'  => str_random(60),
                        
                    ]);
                    $user->save();
                    
                    $data  = [ 'url' => url('api/signup/activate/'.$user->activation_token),'fname' => $user->fname];
                    $email = $user->email;
                    $this->SendMail($data,$email);

                    return response()->json([
                        'message' => 'Thank you for signing up, Please confirm your email address to complete your sign-up process.',
                        'status' => true,
                        'status_code' => 200
                    ]);
               
            }
        }
        else if($userz == 'business')
        {
            $validator = Validator::make($request->all(), [
                'fname'   => 'required|min:3',
                'lname'   => 'required|min:1',
                'email'   => 'required|email|unique:users',
                'mobile'  => 'required|string|unique:users',
                'dob'     => 'required|string',
                'gender'  => 'required|string',
                'user_type' => 'required|string',
                'id_proof'  => 'required|file',
                'profile_pic'   => 'required|file',
                'license'   => 'required|file',
                'password'  => 'required|string',
                
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
                $user = User::where('email',$request->email)->first();
            
                
                    if($request->file('profile_pic'))
                    {
                        $profile_pic = ltrim(Storage::put('public/profile_pic', $request->file('profile_pic')), 'public'); 
                    }else{
                        $profile_pic = ""; 
                    }
                    if($request->file('id_proof'))
                    {
                        $id_proof = ltrim(Storage::put('public/id_proof', $request->file('id_proof')), 'public'); 
                    }else{
                        $id_proof = ""; 
                    }
                    if($request->file('license'))
                    {
                        $license = ltrim(Storage::put('public/license', $request->file('license')), 'public'); 
                    }else{
                        $license = ""; 
                    }
                    $gens = $request->gender;
                    if($gens == 1)
                    {
                        $gender = 'Male';
                    }
                    else if($gens == 2)
                    {
                        $gender = 'Female';
                    }
                    else
                    {
                        return response()->json([
                        'message' => 'Invalid gender type',
                        'status' => false,
                        'status_code' => 422
                    ]);
                    }
                    $user = new User([
                        'fname'             => $request->fname,
                        'lname'             => $request->lname,
                        'email'             => $request->email,
                        'mobile'            => $request->mobile,
                        'password'          => Hash::make(rtrim($request->password)),
                        'dob'               => $request->dob,
                        'gender'            => $request->gender,
                        'user_type'         => $request->user_type,
                        'device_token'      => $request->device_token,
                        'id_proof'          => $id_proof,
                        'profile_pic'          => $profile_pic,
                        'license'           => $license,
                        'activation_token'  => str_random(60),
                        
                    ]);
                    $user->save();
                    $business_user_id = $user->id;
                    $intro = json_decode($request->employee);
                    $getcount = count($intro);
                    for($i=0;$i<$getcount;$i++)
                    {
                        $employee = new Employees([
                        'employee_id' => $intro[$i]->employee_id,
                        'employee_name' => $intro[$i]->employee_name,
                        'business_user_id' => $business_user_id
                    ]);
                    $employee->save();                    
                    }
                    /*echo $intro->college[2]->id; //104
                    $getcount = count($request)*/
                    
                    $data  = [ 'url' => url('api/signup/activate/'.$user->activation_token),'fname' => $user->fname];
                    $email = $user->email;
                    $this->SendMail($data,$email);

                    return response()->json([
                        'message' => 'Thank you for signing up, Please confirm your email address to complete your sign-up process.',
                        'status' => true,
                        'status_code' => 200
                    ]);
               
            }
        }
    }


        
   
    public function SendMail($data, $email)
    {
            Mail::send('emails.email', $data, function($message) use ($email) {
                $message->to($email, 'Samarnas')->subject
                ('Samarnas signup activation');
                $message->from('qa.team@pickzy.com','Samarnas');
            });
    }
    /**
     * Handles Login Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
           // $input = json_decode($request->getContent());
            //print_r($input);
            if(is_numeric($request->email))
            {
             $phone = $request->email;
             $validator = Validator::make($request->all(), [
               'email' => 'required|string',
                    'password' => 'required|string'
                ]);
            /* $validator = Validator::make($request->all(), [
                'mobile' => $phone,
                'password' => $request->password
            ),
                array(
                    'mobile' => 'required|string',
                    'password' => 'required|string',
                     )
            );*/
            if($validator->fails()){

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

            }else{
                $user = User::where('mobile', $phone)->first();  
                //print_r($user);
                if($user){  
                    $device['device_token'] = $request->device_token; 
                    $device['device_type'] = $request->device_type; 
                    User::where('mobile','=',$phone)->update($device); 

                    if(($user->user_type == 1) || ($user->user_type == 2))
                    {
                        if(Auth::attempt(['mobile' => $phone, 'password' => $request->password,'active' => 1]))
                        {
                            if($user->id_proof){
                                $id_proof = "http://temp.pickzy.com/samarnas/storage/app/public/".$user->id_proof;
                            }else{
                                $id_proof = "";
                            }
                            if($user->profile_pic){
                                $profile_pic = "http://temp.pickzy.com/samarnas/storage/app/public/".$user->profile_pic;
                            }else{
                                $profile_pic = "";
                            }
                            $token = auth()->user()->createToken('TutsForWeb')->accessToken;
                            return response()->json([
                                'access_token' => $token,
                                'token_type' => 'Bearer',
                                'message' => trans('messages.login').'!',
                                'status' => true,
                                'status_code' => 200,
                                'user_data' => [ 
                                                'user_id' => $user->id, 
                                                'fname' => $user->fname,
                                                'lname' => $user->lname, 
                                                'profile_pic' => $profile_pic,
                                                'email' => $user->email, 
                                                'mobile' => (string) $user->mobile,
                                                'gender' => $user->gender, 
                                                'dob' => $user->dob, 
                                                'active' => $user->active, 
                                                'device_token' => $user->device_token, 
                                                'user_type' => $user->user_type,]                    
                            ]);

                        }else{
                        
                            if(Hash::check(rtrim($request->password), $user->password)){
                                if(!empty($user->email) && ($user->active == '0')){
                            
                                    return response()->json([
                                        'message' => 'Please confirm your email address to complete your sign-up process.',
                                        'status' => false,
                                        'status_code' => 403
                                    ]);                   
                                }
                            }else{
                                return response()->json([
                                    'message' => 'Kindly, check your password.',
                                    'status' => false,
                                    'status_code' => 422
                                ]);
                            }
                        } 
                    }
                    else if($user->user_type == 3)
                    {
                        if(Auth::attempt(['mobile' => $phone, 'password' => $request->password,'active' => 1]))
                        {
                            if($user->id_proof){
                                $id_proof = "http://temp.pickzy.com/samarnas/storage/app/public/".$user->id_proof;
                            }else{
                                $id_proof = "";
                            }
                            if($user->profile_pic){
                                $profile_pic = "http://temp.pickzy.com/samarnas/storage/app/public/".$user->profile_pic;
                            }else{
                                $profile_pic = "";
                            }
                            $token = auth()->user()->createToken('TutsForWeb')->accessToken;
                            return response()->json([
                                'access_token' => $token,
                                'token_type' => 'Bearer',
                                'message' => trans('messages.login').'!',
                                'status' => true,
                                'status_code' => 200,
                                'user_data' => [ 
                                                'user_id' => $user->id, 
                                                'fname' => $user->fname,
                                                'lname' => $user->lname, 
                                                'email' => $user->email, 
                                                'mobile' => (string) $user->mobile,
                                                'gender' => $user->gender, 
                                                'dob' => $user->dob, 
                                                'profile_pic' => $profile_pic,
                                                'active' => $user->active, 
                                                'device_token' => $user->device_token, 
                                                'user_type' => $user->user_type,]                    
                            ]);

                        }else{
                        
                            if(Hash::check(rtrim($request->password), $user->password)){
                                if(!empty($user->email) && ($user->active == '0')){
                            
                                    return response()->json([
                                        'message' => 'Please confirm your email address to complete your sign-up process.',
                                        'status' => false,
                                        'status_code' => 403
                                    ]);                   
                                }
                            }else{
                                return response()->json([
                                    'message' => 'Kindly, check your password.',
                                    'status' => false,
                                    'status_code' => 422
                                ]);
                            }
                        }
                    }
                }else{
                    return response()->json([
                        'message' => 'Kindly, Register your Mobile Number.',
                        'status' => false,
                        'status_code' => 404
                    ]);
                }
            }
            }
            else
            {
                $validator = Validator::make($request->all(), [
               'email' => 'required|string',
                    'password' => 'required|string'
                ]);
                /*$validator = Validator::make( array(
                'email' => $request->email,
                'password' => $input->password
            ),
                array(
                    'email' => 'required|string|email',
                    'password' => 'required|string',
                     )
            );*/
            if($validator->fails()){

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

            }else{
                $user = User::where('email', $request->email)->first();  
               // print_r($user); exit();
                if($user){  
                    $device['device_token'] = $request->device_token; 
                    $device['device_type'] = $request->device_type; 
                    User::where('email','=',$request->email)->update($device); 

                    if(($user->user_type == 1) || ($user->user_type == 2))
                    {
                        if(Auth::attempt(['email' => $request->email, 'password' => $request->password,'active' => 1]))
                        {
                            if($user->id_proof){
                                $id_proof = "http://temp.pickzy.com/samarnas/storage/app/public/".$user->id_proof;
                            }else{
                                $id_proof = "";
                            }
                            if($user->profile_pic){
                                $profile_pic = "http://temp.pickzy.com/samarnas/storage/app/public/".$user->profile_pic;
                            }else{
                                $profile_pic = "";
                            }
                            $token = auth()->user()->createToken('TutsForWeb')->accessToken;
                            return response()->json([
                                'access_token' => $token,
                                'token_type' => 'Bearer',
                                'message' => trans('messages.login').'!',
                                'status' => true,
                                'status_code' => 200,
                                'user_data' => [ 'user_id' => $user->id, 'fname' => $user->fname, 
                                                'lname' => $user->lname, 'email' => $user->email, 'mobile' => (string) $user->mobile,'profile_pic' => $profile_pic,'gender' => $user->gender, 'dob' => $user->dob, 'active' => $user->active, 'device_token' => $user->device_token, 'user_type' => $user->user_type,'categoryStatus'=>'']                    
                            ]);

                        }else{
                        
                            if(Hash::check(rtrim($request->password), $user->password)){
                                if(!empty($user->email) && ($user->active == '0')){
                            
                                    return response()->json([
                                        'message' => 'Please confirm your email address to complete your sign-up process.',
                                        'status' => false,
                                        'status_code' => 403
                                    ]);                   
                                }
                            }else{
                                return response()->json([
                                    'message' => 'Kindly, check your password.',
                                    'status' => false,
                                    'status_code' => 422
                                ]);
                            }
                        } 
                    }
                    else if(($user->user_type == 3) || ($user->user_type == 4))
                    {
                        //echo "adfsadfsdf"; exit();
                        if(Auth::attempt(['email' => $request->email, 'password' => $request->password,'active' => 1]))
                        {
                            if($user->id_proof){
                                $id_proof = "http://temp.pickzy.com/samarnas/storage/app/public/".$user->id_proof;
                            }else{
                                $id_proof = "";
                            }
                            if($user->profile_pic){
                                $profile_pic = "http://temp.pickzy.com/samarnas/storage/app/public/".$user->profile_pic;
                            }else{
                                $profile_pic = "";
                            }
                            $extra_data = Extras::select('latitude','longitude')->where('provider_id','=', $user->id)->get();
                            $token                      = auth()->user()->createToken('TutsForWeb')->accessToken;
                            $ProviderSubcategory        = ProviderSubcategory::where('provider_id','=', $user->id)->get()->toArray();
                            if(!empty($ProviderSubcategory))
                            {
                                    $categoryStatus     = "1";
                            }
                            else
                            {
                                    $categoryStatus     = "0";
                            }
                            return response()->json([
                                'access_token' => $token,
                                'token_type' => 'Bearer',
                                'message' => trans('messages.login').'!',
                                'status' => true,
                                'status_code' => 200,
                                'user_data' => [ 'user_id' => $user->id, 'fname' => $user->fname, 
                                                'lname' => $user->lname, 'email' => $user->email, 'mobile' => (string) $user->mobile,'profile_pic' => $profile_pic,'gender' => $user->gender, 'dob' => $user->dob, 'active' => $user->active, 'device_token' => $user->device_token, 'user_type' => $user->user_type,'categoryStatus'=>$categoryStatus] 

                            ]);

                        }else{
                        
                            if(Hash::check(rtrim($request->password), $user->password)){
                                if(!empty($user->email) && ($user->active == '0')){
                            
                                    return response()->json([
                                        'message' => 'Please confirm your email address to complete your sign-up process.',
                                        'status' => false,
                                        'status_code' => 403
                                    ]);                   
                                }
                            }else{
                                return response()->json([
                                    'message' => 'Kindly, check your password.',
                                    'status' => false,
                                    'status_code' => 422
                                ]);
                            }
                        }  
                    }
                }
                else
                {
                    return response()->json([
                        'message' => 'Kindly, Register your Email.',
                        'status' => false,
                        'status_code' => 404
                    ]);
                }
            }
            }
            
            
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function socialVerify(Request $request)
    {
        $input = json_decode($request->getContent());     
        $user = User::find($input->user_id)->update(['social_verify' => $input->social_verify,'social_login_type' => $input->social_verify]);  
       
        return response()->json([
            'message' => 'Your profile is successfully verified.',
            'status' => true,
            'status_code' => 200
        ]);
    }
    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'status' => true,
            'message' => 'Successfully logged out',
            'status_code' => 200,
        ]);
    }
    /*public function getuserlocation(Request $request)
    {
        $user = User::find(auth()->user()->id); 
        $id = $user->id;
        $infoArray = Locations::where('user_id','=', $id)->get()->toArray();
        if(!empty($infoArray))
        {
            foreach ($infoArray as $key => $value) 
            {
                $infoArrays[$key]['fulladdress']       => $value['fulladdress'],
                $infoArrays[$key]['user_id']           => $id,
                $infoArrays[$key]['flatno']            => $value['flatno'],
                $infoArrays[$key]['landmark']          => $value['landmark'],
                $infoArrays[$key]['location_type']     => $value['location_type'],
                $infoArrays[$key]['pincode']           => $value['pincode'],
                $infoArrays[$key]['latitude']          => $value['latitude'],
                $infoArrays[$key]['longitude']         => $value['longitude']

            }

        return response()->json([
            'message' => 'About us Content',
            'status' => true,
            'status_code' => 200,
            'result' => $infoArrays
        ]); 
        }
        else{
            return response()->json([
            'message' => 'About us Content',
            'status' => true,
            'status_code' => 200,
            'result' => $infoArray
        ]); 
        } */
    public function signupActivate($token)
    {
        $user = User::where('activation_token', $token)->first();
        if (!$user) {
            $message = 'This activation token is invalid.';
            return view('active',compact('message'));
        }
        $user->active = true;
        $user->activation_token = '';
        $user->save();
        
        $message = 'Thank you!.';
        return view('active',compact('message'));
    }
    /**
     * Returns Authenticated User Details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $user = User::find(auth()->user()->id);
       // print_r($user);
       // exit();
        if(Hash::check($request->old_password, $user->password)) 
            {
            User::find($user->id)->update(['password' => Hash::make(rtrim($request->new_password))]);
            $message = "Password updated successfully";
             return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => $message
        ]);
            
        }else{
            $message = "Incorrect old password";
             return response()->json([
            'status' => true,
            'status_code' => 405,
            'message' => $message
        ]);
            
        }
        
    }
    public function getuserlocation(Request $request)
    {
        $user = User::find(auth()->user()->id); 
        $id = $user->id;
        $infoArray = Locations::where('user_id','=', $id)->get()->toArray();
        //print_r($infoArray); exit();
        if(!empty($infoArray))
        {
            foreach ($infoArray as $key => $value) 
            {
                $infoArrays[$key]['address_id']        = $value['address_id'];
                $infoArrays[$key]['fulladdress']       = $value['fulladdress'];
                $infoArrays[$key]['user_id']           = $id;
                $infoArrays[$key]['flatno']            = $value['flatno'];
                $infoArrays[$key]['landmark']          = $value['landmark'];
                $infoArrays[$key]['location_type']     = $value['location_type'];
                $infoArrays[$key]['pincode']           = $value['pincode'];
                $infoArrays[$key]['latitude']          = $value['latitude'];
                $infoArrays[$key]['longitude']         = $value['longitude'];

            }

        return response()->json([
            'message' => 'Successfully Showed',
            'status' => true,
            'status_code' => 200,
            'result' => $infoArrays
        ]); 
        }
        else{
            return response()->json([
            'message' => 'No Address Found',
            'status' => true,
            'status_code' => 200,
            'result' => $infoArray
        ]); 
        }
    } 
    public function deletelocation(Request $request)
    {
        $user_id  = Auth::user()->id; 
        $location = Locations::where([['address_id','=', $request->address_id],['user_id', '=', $user_id]])->first();
        //print_r($location['address_id']); exit();
        if($location['address_id']!='')
        {
            
            Locations::where([['address_id','=', $request->address_id],['user_id', '=', $user_id]])->delete();
        //Extras::where('provider_id', $user->id)->first();
                    return response()->json([
                        'message' => 'Delete the location',
                        'status' => true,
                        'status_code' => 200
                    ]);
        }
        else
        {
            return response()->json([
                        'message' => 'Address Already deleted',
                        'status' => true,
                        'status_code' => 200
                    ]);
        }
        

    }
    public function userlocation(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
                'fulladdress'   => 'required|min:8',
                'flatno'   => 'required|min:1',
                'landmark'  => 'required|string',
                'location_type'   => 'required|string',
                'pincode'  => 'required|min:3',
                'latitude'   => 'required|string',
                'longitude'  => 'required|string'                
            ]);
        if($validator->fails()){

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

            }else{
                $user = User::find(auth()->user()->id); 
                $id = $user->id; 
                $users = Locations::where([['location_type','=', $request->location_type],['user_id', '=', $id]])->get()->toArray();
             /*print_r($users); 
             echo $users[0]['address_id']; exit();*/
            if(empty($users[0]['address_id']))
            { 
                $useradd = new Locations([
                        'fulladdress'       => $request->fulladdress,
                        'user_id'           => $id,
                        'flatno'            => $request->flatno,
                        //'blockno'           => $request->blockno,
                        'landmark'          => $request->landmark,
                        'location_type'     => $request->location_type,
                        'pincode'           => $request->pincode,
                        'latitude'          => $request->latitude,
                        'longitude'         => $request->longitude
                    ]);
                $useradd->save();
                $message = "Location Added successfully";
                return response()->json([
                    'status' => true,
                    'status_code' => 200,
                    'message' => $message
                ]);
            
            }
            else
            {
                $user = User::find(auth()->user()->id); 
                $id = $user->id;
                $userzz = Locations::where([['location_type','=', $request->location_type],['user_id', '=', $id]])->update(['fulladdress'=>$request->fulladdress,'user_id'=>$id,'flatno'=>$request->flatno,'landmark'=>$request->landmark,'location_type'=>$request->location_type,'pincode'=> $request->pincode,'latitude'=>$request->latitude,'longitude'=>$request->longitude]); 
                $message = "Location Updated Successfully";
                 return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => $message
                ]);
            
            }
        
        }
    }
    public function forgotPassword(Request $request)
    {

        if($request->action_type == 1){
            $user = Auth::user();

           $users = User::where('email','=',$request->email)->get()->toArray();
          if($users)
          {
           
            if($users['0']['active'] == 1)
            {

             $data  = [ 'url' => url('api/reset?email='.$request->email.'&method=reset')];
            AuthController::SendForgotMail($data,$request->email);

            return response()->json([
                'message' => 'Reset link sent to your email.',
                'status' => true,
                'status_code' => 200,
                'action_type' => $request->action_type
            ]);
            }
            else
            {
                 return response()->json([
                'message' => 'Please Confirm your email id',
                'status' => false,
                'status_code' => 404
            ]);
            }

           
          
          }
           else
           {
             return response()->json([
                'message' => 'Please enter registered email id',
                'status' => false,
                'status_code' => 404
                
            ]);
          }

           
        }else{
            $email = $request->email;
            $password = $request->password;        
            User::where('email',$request->email)->update([ 'password'=> Hash::make(rtrim($request->password))]);
            return response()->json([
                'message' => 'Password reset successfully.',
                'status' => true,
                'status_code' => 200,
                'action_type' => $request->action_type

            ]);
        }
    }
   public function SendForgotMail($data, $email)
    {
            Mail::send('emails.forgot', $data, function($message) use ($email) {
                $message->to($email, 'Samarnas')->subject
                ('Reset Password');
                $message->from('qa.team@pickzy.com','Samarnas');
            });
    }
    public function resetPassword(Request $request)
    {
       $email = $request->input('email');
       $method = $request->input('method');

        return view('reset',compact('email','method'));
    }

     /********provider login*****/
public function provider_login(Request $request)
    {
            if(is_numeric($request->email))
            {
             $phone = $request->email;
             $validator = Validator::make($request->all(), [
               'email' => 'required|string',
                    'password' => 'required|string'
                ]);
            
            if($validator->fails()){

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

            }else{
                $user = User::where('mobile', $phone)->first(); 
                $extra_data =  Extras::where('provider_id', $user->id)->first();
                //print_r($extra_data); exit();
                if($user){  
                    $device['device_token'] = $request->device_token; 
                    $device['device_type'] = $request->device_type; 
                    User::where('mobile','=',$phone)->update($device); 

                    if($user->user_type == 3)
                    {
                        if(Auth::attempt(['mobile' => $phone, 'password' => $request->password,'active' => 1]))
                        {
                            if($user->id_proof){
                                $id_proof = "http://temp.pickzy.com/samarnas/storage/app/public/".$user->id_proof;
                            }else{
                                $id_proof = "";
                            }
                            if($user->profile_pic){
                                $profile_pic = "http://temp.pickzy.com/samarnas/storage/app/public/".$user->profile_pic;
                            }else{
                                $profile_pic = "";
                            }
                            $token = auth()->user()->createToken('TutsForWeb')->accessToken;
                            return response()->json([
                                'access_token' => $token,
                                'token_type' => 'Bearer',
                                'message' => trans('messages.login').'!',
                                'status' => true,
                                'status_code' => 200,
                                'user_data' => [ 'user_id' => $user->id, 'fname' => $user->fname,'lname' => $user->lname, 'email' => $user->email, 'mobile' => (string) $user->mobile,'id_proof' => $id_proof,'gender' => $user->gender, 'dob' => $user->dob, 'active' => $user->active, 'device_token' => $user->device_token, 'user_type' => $user->user_type,]                    
                            ]);

                        }else{
                        
                            if(Hash::check(rtrim($request->password), $user->password)){
                                if(!empty($user->email) && ($user->active == '0')){
                            
                                    return response()->json([
                                        'message' => 'Please confirm your email address to complete your sign-up process.',
                                        'status' => false,
                                        'status_code' => 403
                                    ]);                   
                                }
                            }else{
                                return response()->json([
                                    'message' => 'Kindly, check your password.',
                                    'status' => false,
                                    'status_code' => 422
                                ]);
                            }
                        } 
                    }
                }else{
                    return response()->json([
                        'message' => 'Kindly, Register your Mobile Number.',
                        'status' => false,
                        'status_code' => 404
                    ]);
                }
            }
            }
            else
            {
                $validator = Validator::make($request->all(), [
               'email' => 'required|string',
                    'password' => 'required|string'
                ]);
                /*$validator = Validator::make( array(
                'email' => $request->email,
                'password' => $input->password
            ),
                array(
                    'email' => 'required|string|email',
                    'password' => 'required|string',
                     )
            );*/
            if($validator->fails()){

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

            }else{
                $user = User::where('email', $request->email)->first();  
                $extra_data =  Extras::where('provider_id', $user->id)->first();
                //print_r($extra_data); exit();
                if($user){  
                    $device['device_token'] = $request->device_token; 
                    $device['device_type'] = $request->device_type; 
                    User::where('email','=',$request->email)->update($device); 

                    if($user->user_type == 3)
                    {
                        if(Auth::attempt(['email' => $request->email, 'password' => $request->password,'active' => 1]))
                        {
                            if($user->id_proof){
                                $id_proof = "http://temp.pickzy.com/samarnas/storage/app/public/".$user->id_proof;
                            }else{
                                $id_proof = "";
                            }
                            if($user->profile_pic){
                                $profile_pic = "http://temp.pickzy.com/samarnas/storage/app/public/".$user->profile_pic;
                            }else{
                                $profile_pic = "";
                            }
                            $token = auth()->user()->createToken('TutsForWeb')->accessToken;
                            return response()->json([
                                'access_token' => $token,
                                'token_type' => 'Bearer',
                                'message' => trans('messages.login').'!',
                                'status' => true,
                                'status_code' => 200,
                                'user_data' => [ 'user_id' => $user->id, 'fname' => $user->fname, 
                                                'lname' => $user->lname, 'email' => $user->email, 'mobile' => (string) $user->mobile,'id_proof' => $id_proof,'profile_pic' => $profile_pic,'gender' => $user->gender, 'dob' => $user->dob, 'active' => $user->active, 'device_token' => $user->device_token, 'user_type' => $user->user_type,'location_data' => $extra_data,] 

                            ]);

                        }else{
                        
                            if(Hash::check(rtrim($request->password), $user->password)){
                                if(!empty($user->email) && ($user->active == '0')){
                            
                                    return response()->json([
                                        'message' => 'Please confirm your email address to complete your sign-up process.',
                                        'status' => false,
                                        'status_code' => 403
                                    ]);                   
                                }
                            }else{
                                return response()->json([
                                    'message' => 'Kindly, check your password.',
                                    'status' => false,
                                    'status_code' => 422
                                ]);
                            }
                        } 
                    }
                }else{
                    return response()->json([
                        'message' => 'Kindly, Register your Email.',
                        'status' => false,
                        'status_code' => 404
                    ]);
                }
            }
            }
            
            
    }





    /********provider register*****/
    public function provider_register(Request $request)
    {  
    // if(!empty($request->id_proof))  
    // {
    //     return response()->json([
    //                     'message' => $request->id_proof."get images",
    //                     'status' => false,
    //                     'status_code' => 422
    //                 ]);
    //     exit();
    // } 
    // else
    // {
    //     return response()->json([
    //                     'message' => "no get id proof images",
    //                     'status' => false,
    //                     'status_code' => 422
    //                 ]);
    //     exit();
    // }
        if($request->user_type == 3)
        {
            $validator = Validator::make($request->all(), [
                'fname'             => 'required|min:3',
                'lname'             => 'required|min:1',
                'email'             => 'required|email|unique:users',
                'mobile'            => 'required|string|unique:users',
                'dob'               => 'required|string',
                'gender'            => 'required|string',
                'id_proof'          => 'required',
                'password'          => 'required|string',
                'confirm_password'  => 'required|string',
                'profile_pic'       => 'required|file',
                'address'           => 'required|string',
                'city'              => 'required|string',
                'state'             => 'required|string',
                'zip_code'          => 'required|string',
                //'experience_doc'    => 'required',
                'language'          => 'required',
                'country_name'          => 'required',
                'country_id'          => 'required',
            ]);
        }
        else if($request->user_type == 4)
        {
            $validator = Validator::make($request->all(), [
                'fname'             => 'required|min:3',
                'profile_pic'       => 'required|file',
                'lname'             => 'required|min:1',
                'email'             => 'required|email|unique:users',
                'mobile'            => 'required|string|unique:users',
                'dob'               => 'required|string',
                'gender'            => 'required|string',
                'id_proof'          => 'required',
                'password'          => 'required|string',
                'confirm_password'  => 'required|string',
                // 'service'           => 'required|string',
                'address'           => 'required|string',
                'city'              => 'required|string',
                'state'             => 'required|string',
                'zip_code'          => 'required|string',
                //'experience_doc'    => 'required',
                'bussiness_license' => 'required|file',
                'language'          => 'required',
                'country_name'          => 'required',
                'country_id'          => 'required',
            ]);
        }
        else
        {
             return response()->json([
                        'message' => "Invalid user type.",
                        'status' => false,
                        'status_code' => 422
                    ]);
        }
      
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
                $user = User::where('email',$request->email)->first();
                    if($request->file('id_proof'))
                    {
                        foreach ($request->file('id_proof') as $key => $value) 
                        {
                            $id_proofs  = ltrim(Storage::put('public/id_proof', $value), 'public');
                            $id_proofz[] = $id_proofs;
                        }
                        $id_proof   = implode(',',$id_proofz);
                    }else{
                        $id_proof = ""; 
                    }
                    if($request->file('experience_doc'))
                    {
                        foreach ($request->file('experience_doc') as $key => $value) 
                        {
                            $experience_docs = ltrim(Storage::put('public/experience_doc', $value), 'public');
                            $experience_docz[]= $experience_docs;
                        }
                        $experience_doc   = implode(',',$experience_docz);
                        //$experience_doc = ltrim(Storage::put('public/experience_doc', $request->file('experience_doc')), 'public'); 
                    }else{
                        $experience_doc = ""; 
                    }

                    if($request->file('bussiness_license'))
                    {
                        $bussiness_license = ltrim(Storage::put('public/bussiness_license', $request->file('bussiness_license')), 'public'); 
                    }else{
                        $bussiness_license = ""; 
                    }

                    if($request->file('profile_pic'))
                    {
                        $profile_pic = ltrim(Storage::put('public/profile_pic', $request->file('profile_pic')), 'public'); 
                    }else{
                        $profile_pic = ""; 
                    }
                    $gens = $request->gender;
                    if($gens == 1)
                    {
                        $gender = 'Male';
                    }
                    else if($gens == 2)
                    {
                        $gender = 'Female';
                    }
                    else
                    {
                        return response()->json([
                        'message' => 'Invalid gender type',
                        'status' => false,
                        'status_code' => 422
                    ]);
                    }
                    $user = new User([
                        'fname'             => $request->fname,
                        'lname'             => $request->lname,
                        'email'             => $request->email,
                        'mobile'            => $request->mobile,
                        'password'          => Hash::make(rtrim($request->password)),
                        'dob'               => $request->dob,
                        'gender'            => $gender,
                        'user_type'         => $request->user_type,
                        'device_token'      => $request->device_token,
                        'id_proof'          => count($id_proofz),
                        'activation_token'  => str_random(60),
                        'profile_pic'       => $profile_pic,
                        
                    ]);
                    $user->save();
                    
                    
                    foreach($id_proofz as $key => $id_proofzz)
                    {
                        $employee = new ProviderDocument([
                        'document_url' => $id_proofzz,
                        'provider_id' => $user->id,
                        'document_type' => 'id_proof',
                        'document_status' => 'pending',
                        
                    ]);
                    
                    $employee->save();                    
                    }
                    foreach($experience_docz as $key => $experience_doczz)
                    {
                        $employee = new ProviderDocument([
                        'document_url' => $experience_doczz,
                        'provider_id' => $user->id,
                        'document_type' => 'experience_doc',
                        'document_status' => 'pending',
                        
                    ]);
                    $employee->save();                    
                    }
                    $address = $request->address.','.$request->city.','.$request->state.','.$request->zip_code;
                     $latLong = $this->getLatLong($address);
                     $latitude = $latLong['latitude'];
                     $longitude = $latLong['longitude'];
                    // $latitude = "65.55.22";
                    // $longitude = "55.66.22";
                    // echo $latitude;
                    // exit();
                    $extras = new Extras([
                        // 'service'        => $request->service,
                        'address'        => $request->address,
                        'city'           => $request->city,
                        'state'          => $request->state,
                        'zip_code'       => $request->zip_code,
                        'experience_doc' => $experience_doc,
                        'bussiness_license' => $bussiness_license,
                        'latitude'       => $latitude,
                        'longitude'      => $longitude,
                        'provider_id'    => $user->id,
                        'language'       => $request->language,
                        'country_name'   => $request->country_name,
                        'country_id'     => $request->country_id,
                    ]);

                    $extras->save();
                    $data  = [ 'url' => url('api/signup/activate/'.$user->activation_token),'fname' => $user->fname];
                    $email = $user->email;
                    $this->SendMail($data,$email);

                    return response()->json([
                        'message' => 'Thank you for signing up, Please confirm your email address to complete your sign-up process.',
                        'status' => true,
                        'status_code' => 200
                    ]);
               
            }
        
    }
    public function getLatLong($address)
    {
        if(!empty($address))
        {
        //Formatted address
        $formattedAddr = str_replace(' ','+',$address);
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyBmgPYOmv0iHT8bJHWNTtBRhTV5-JfFJwA&address='.$formattedAddr.'&sensor=true';
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $contents = curl_exec($ch);
        if (curl_errno($ch)) {
          echo curl_error($ch);
          echo "\n<br />";
          $contents = '';
        } else {
          curl_close($ch);
        }

        if (!is_string($contents) || !strlen($contents)) {
        echo "Failed to get contents.";
        $contents = '';
        }

        $output = json_decode($contents);
        //Get latitude and longitute from json data
        if(!empty($output->results))
        {
         $data['latitude']  = $output->results[0]->geometry->location->lat; 
         $data['longitude'] = $output->results[0]->geometry->location->lng;
        }
        else
        {
            $data = array();
        }
        //Return latitude and longitude of the given address
            if(!empty($data))
            {
                return $data;
            }
            else
            {
                return false;
            }
        }  
        else
        {
            return false;   
        }
    }
    public function getprofile(Request $request)
    {
        $infoArray = [];
        $user_id = $request->id;
        $infoArray = User::where('id','=', $user_id)->get()->toArray();
       // print_r($infoArray); exit();
        if(!empty($infoArray))
        {
            foreach ($infoArray as $key => $value) 
            {
               $infoArrays[$key]['fname']  = $value['fname'];
               $infoArrays[$key]['lname']  = $value['lname'];
               $infoArrays[$key]['email']  = $value['email'];
               $infoArrays[$key]['gender'] = $value['gender'];
               $infoArrays[$key]['dob']    = $value['dob'];
               $infoArrays[$key]['password']  ='';
               $infoArrays[$key]['profile_pic']    = "http://temp.pickzy.com/samarnas/storage/app/public/".$value['profile_pic'];
               
            }

        return response()->json([
            'message' => 'Profile Details showed successful!',
            'status' => true,
            'status_code' => 200,
            'result' => $infoArrays
        ]); 
        }
        else{
            return response()->json([
            'message' => 'Profile Details showed successful!',
            'status' => true,
            'status_code' => 200,
            'result' => $infoArray
        ]); 
        } 
    }
    public function editprofile(Request $request)
    {
        //echo  $request->file('profile_pic'); exit(); 
        if($request->profile_pic !='')
            {
                $profile_pic = ltrim(Storage::put('public/profile_pic', $request->file('profile_pic')), 'public'); 
            }
        else
            {
                $infoprofile = User::where('id','=', $request->id)->get()->toArray();
                       //print_r($infoprofile); exit();
                     
                        $profile_pic = $infoprofile[0]['profile_pic']; 
            }
        User::where('id',$request->id)->update(['fname'=> $request->fname,'lname'=>$request->lname,'email'=>$request->email,'gender'=>$request->gender,'dob'=>$request->dob,'profile_pic'=>$profile_pic]);
        $infoArray = User::where('id','=', $request->id)->get()->toArray();
       // print_r($infoArray); exit();
        if(!empty($infoArray))
        {
            foreach ($infoArray as $key => $value) 
            {
               $infoArrays[$key]['fname']  = $value['fname'];
               $infoArrays[$key]['lname']  = $value['lname'];
               $infoArrays[$key]['email']  = $value['email'];
               $infoArrays[$key]['gender'] = $value['gender'];
               $infoArrays[$key]['dob']    = $value['dob'];
               $infoArrays[$key]['password']  ='';
               $infoArrays[$key]['profile_pic']    = "http://temp.pickzy.com/samarnas/storage/app/public/".$value['profile_pic'];
               
            }
        }
            return response()->json([
                'message' => 'Profile updated successfully.',
                'status' => true,
                'status_code' => 200,
                'result' => $infoArrays

            ]);
    }
    
}
