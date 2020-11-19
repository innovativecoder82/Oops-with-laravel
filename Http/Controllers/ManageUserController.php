<?php

namespace samarnas\Http\Controllers;

use samarnas\gender_types;
use samarnas\services;
use samarnas\categorys;
use samarnas\User;
use samarnas\gender_service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use DB;

class ManageUserController extends Controller
{
    public function index()
    {
       $user = User::where('user_type','=','1')->get()->toArray(); 
       return view('manage_user', compact('user'));
    }

    public function view(Request $request)
    {
        $id = $request->input('id');
        $user = User::where('id',$id)->get()->toArray(); 
         //print_r($user); exit();
        foreach ($user as $key => $value) {
        	# code...
        }
        return view('manage_userView', compact('value'));
    }

    public function destroy(Request $request)
    {
     
        User::where('id',$request->id)->delete(); //print_r($rests); exit();
        return back()->with('success', 'User Deleted Successfully');
    }
}
