<?php

namespace samarnas\Http\Controllers;
use samarnas\categorys;
use samarnas\services;
use samarnas\Admin;
use Illuminate\Http\Request;
use samarnas\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use DB;
use Hash;
use Session;
use Auth;

class AdminController extends Controller
{
    
    public function login(Request $request)
    {
      // print_r($request->password); exit();
        $email = $request->email; 
        $password = md5($request->password);  //print_r($password); exit();
        $checkAdmin = Admin::where([['email','=',$email],['password','=',$password]])->get()->toArray();
      //print_r($checkAdmin);
     // echo count($checkAdmin); 
      if(count($checkAdmin) != '0')
        { 
          $data = Admin::where('email','=',$email)->first(); 

            if($data) {// whether or not the email exists 
                //echo "adfsfdasf"; exit();
                    Session::put('id', $data->id); 
                    Session::put('email', $data->email); 
                    Session::put('login', TRUE); 
                    return view('index');
                      
                 }
        }
        else
        {
          return back()->with('success','Email or Password, Incorrect!');
        }
      }

     public function logout()
        {
          Auth::logout();
          return redirect('/');
        }

    public function edit(Request $request)
      { 
          $profile = $_POST['id'];  //dd($admin_id);
          return view('profile_edit', compact('profile'));
      }

    public function update(Request $request)
    { 
       // dd($request->id);
        $old_password = $request->old_password;
        $new_password = $request->new_password; 
        $confirm_password = $request->confirm_password; 

        $data = Admin::find($request->prof_id);
        if(!empty($data) && Hash::check($old_password, $data->password))
        {
            if($new_password == $confirm_password)
            {
                Admin::where('id',$request->prof_id)->update(['password'=> Hash::make($new_password)]);
                return back()->with('success', 'Password Changed Successfully');
            }
            else
            {
                return back()->with('success', 'Incorrect Confirm Password');
            }
        }
        else
        {
            return back()->with('success', 'Incorrect Old Password');
        }
    }
}
