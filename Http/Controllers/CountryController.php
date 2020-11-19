<?php

namespace samarnas\Http\Controllers;

use samarnas\categorys;
use samarnas\services;
use samarnas\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use DB;

class CountryController extends Controller
{

	public function index()
    {
		$country = Country::all()->toArray();
	    return view('manage_country', compact('country'));
	}

    public function store(Request $request)
    {
       $country = new Country([
            'country_name'    =>      $request->country_name,
            'country_code'    =>      $request->country_code
        ]);
        $country->save();
        return back()->with('success', 'Country Inserted');
    }

    public function view(Request $request)
    {
    	$id = $request->country_id; 
        $country = Country::where('country_id',$id)->get()->toArray();
        foreach ($country as $key => $value) {
            
         }  
        return view('countryView', compact('value'));
    }

    public function show(Request $request)
    {  
        $id = $request->input('country_id');
        $country = Country::where('country_id',$id)->get()->toArray(); 
        foreach ($country as $key => $countrys) {
            
         } 
        return view('countryEdit', compact('countrys'));
    }


    public function update(Request $request)
    {
        Country::where('country_id',$request->country_id)->update(['country_name'=> $request->country_name,'country_code' => $request->country_code]);
        return back()->with('success', 'Country Updated');
    }

    public function destroy(Request $request)
    {
        Country::where('country_id',$request->country_id)->delete();
        return back()->with('success', 'Country Deleted');
    }


}
