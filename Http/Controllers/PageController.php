<?php

namespace samarnas\Http\Controllers;
use Illuminate\Http\Request;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Validator;
use Auth;
use DB;
use samarnas\Pages;
class PageController extends Controller
{
public function getabout(Request $request)
    {
        $infoArray = Pages::where('page_id','=', '1')->get()->toArray();
        if(!empty($infoArray))
        {
            foreach ($infoArray as $key => $value) 
            {
               // $content = html_entity_decode($value['content']); exit();
               // $content = htmlentities($value['content']); exit();
               $infoArrays[$key]['title']  = $value['title'];
               $infoArrays[$key]['content']  = $value['content'];

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
        } 
    }
}
