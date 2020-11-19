<?php

namespace samarnas\Http\Controllers;
use samarnas\Categorys;
use samarnas\Services;
use samarnas\Price;
use samarnas\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use DB;

class PriceController extends Controller
{
    
    public function index()
    {
       if($_REQUEST['id'] == 0)
       {
           $country = Country::all()->toArray();
           $price = Price::all()->toArray(); 
           foreach ($price as $key => $pr) {
            
           } 
       }
       $id = $_REQUEST['id'];
       return view('manage_price', compact('country','id'));
    }
    
    public function viwed($id)
    {
      $country = Country::get()->toArray();
      
      if($id != 'Choose Country')
      {
      $service    = categorys::all()->toArray(); 

      foreach ($service as $key => $value) 
      {
          $db = DB::table('prices')->select('amount','price_id')->where('country_id','=',$id)->where('service_id','=',$value['sub_category_id'])->get();
          $db   = json_decode(json_encode($db),true);
          if(!empty($db))
          {
            $service[$key]['amount']    = $db[0]['amount'];
            $service[$key]['price_id']  = $db[0]['price_id'];
          }
          else
          {
            $service[$key]['amount']    = '';
            $service[$key]['price_id']  = '';
          }
      }
      return view('manage_price', compact('service','country','id')); 
      }
      else
      {
        $country = array();
        $service = array();
        return view('manage_price', compact('service','country','id'));
      }
    }

    public function getServicelist()
    { 
     //  print_r($_POST['id']); exit();
      $country = Country::where('country_id',$_POST['id'])->get()->toArray();
      if($_POST['id'] != 'Choose Country')
      {
      $service    = categorys::all()->toArray(); 

      foreach ($service as $key => $value) 
      {
          $db = DB::table('prices')->select('amount','price_id')->where('country_id','=',$_POST['id'])->where('service_id','=',$value['sub_category_id'])->get();
          $db   = json_decode(json_encode($db),true);
          if(!empty($db))
          {
            $service[$key]['amount']    = $db[0]['amount'];
            $service[$key]['price_id']  = $db[0]['price_id'];
          }
          else
          {
            $service[$key]['amount']    = '';
            $service[$key]['price_id']  = '';
          }
      }
      return view('getServicelist', compact('service','country')); 
      }
      else
      {
        $country = array();
        $service = array();
        return view('getServicelist', compact('service','country')); 
      }
    }

    public function view(Request $request)
    {        
        // print_r($_POST['sub_category_id']); exit();
        $id = $request['sub_category_id'];
        $country_id = $request['country_id'];
        $service = categorys::where('sub_category_id',$id)->get()->toArray(); 
        foreach ($service as $key => $value) {
 
        }
        $price = Price::where('service_id',$id)->where('country_id',$country_id)->get()->toArray();
        foreach ($price as $key => $price) {
          # code...
        }
        return view('priceView', compact('value','price'));
    }

    public function show(Request $request)
    {  
        $id = $request['sub_category_id']; 
        $country_name = $request['country_name']; 
        $country_id = $request['country_id']; 
        $category = categorys::where('sub_category_id',$id)->get()->toArray();
        $price = Price::where('service_id',$id)->where('country_id',$country_id)->get();
        $price   = json_decode(json_encode($price),true);
        foreach ($price as $key => $price) {
          # code...
        }
        return view('priceEdit', compact('country_name','country_id','category','price'));
    }

    public function update(Request $request)
    {
    
       $country_id =  $request->country_id; 
      if($request->price_id != '')
      {
        Price::where('price_id',$request->price_id)->update(['country_id'=> $request->country_id,'service_id'=>$request->sub_category_id,'amount' => $request->amount]);
        return $this->viwed($country_id);
      }
      else
      {
        $country = new Price([
            'country_id'    =>      $request->country_id,
            'service_id'    =>      $request->sub_category_id,
            'amount'        =>      $request->amount
        ]);
       
        $country->save();
        return $this->viwed($country_id);
       // return back()->with('success', 'Price Inserted');
      }
      
    }

    public function getDelete(Request $request)
    {
        $price_id = $request['price_id'];
        $country_id = $request['country_id'];
        return view('priceDelete', compact('price_id','country_id'));
    }

    public function destroy(Request $request)
    {   
        Price::where('price_id',$request->price_id)->delete();
        $country_id = $request['country_id'];
        return $this->viwed($country_id);
        //return view('priceEdited'); 
    }   

}
