<?php

namespace samarnas\Http\Controllers;

use samarnas\Gender_types;
use samarnas\Services;
use samarnas\Categorys;
use samarnas\User;
use samarnas\Admin;
use samarnas\Extras;
use samarnas\ProviderServices;
use samarnas\gender_service;
use samarnas\Orderdetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use DB;

class PaymentController extends Controller
{
 
     public function index()
     {
        $customer = Orderdetails::join('users', 'users.id','=','orderdetails.user_id')->get()->toArray();
        /*print_r("<pre>");
        print_r($customer);
        print_r("</pre>");
        exit();*/
        return view('customer_transaction', compact('customer'));
     }
     
    public function custView(Request $request)
    {        
        $id = $request->id; //print_r($id); exit();
        $customer = Orderdetails::join('users', 'users.id','=','orderdetails.user_id')->where('user_id',$id)->get()->toArray(); 
        foreach ($customer as $key => $cust_view) {
            
         }  
        return view('cust_transactionView', compact('cust_view'));
    }
    
}