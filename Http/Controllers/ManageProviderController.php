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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use DB;

class ManageProviderController extends Controller
{
    public function index()
    {
    // $provider = User::where('user_type','=','3')->where('user_type','=','4')->get()->toArray(); 
       $provider = User::where('user_type','!=','1')->get()->toArray(); 
   
       $services = services::all()->toArray(); //print_r($services); exit();
       return view('manage_provider', compact('provider','services'));
    }

    public function view(Request $request)
    {
        $id = $request->id; 
        $provider = User::where('id',$id)->get()->toArray(); 
        foreach ($provider as $key => $value) {
          
        } 
        $ProviderServices   = new ProviderServices();
        $services = $ProviderServices->getservice($id); // print_r($services); exit();
        foreach ($services as $serve => $ser) {
           
        }  
        $Extras = new Admin();  // print_r($extras); exit();
        $extras = $Extras->getProvider($id);  
        return view('manage_providerView', compact('value','ser','extras'));
        }

    public function delete(Request $request)
    {
        // echo "Adfdsafsdsd"; exit();
        User::where('id',$request->id)->delete(); //print_r($rests); exit();
        return back()->with('success', 'Provider Deleted Successfully');
    }
}
