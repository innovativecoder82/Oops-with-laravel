<?php

namespace samarnas\Http\Controllers;
use samarnas\Categorys;
use samarnas\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use DB;
class CategoryController extends Controller
{
    
    public function index()
    {
       $categories = \DB::table('categorys')
                      ->join('services', 'services.service_id','=','categorys.service_id')
                      ->select('services.service_id as serve_id','categorys.*','services.service_name')
                      ->orderBy('sub_category_id','asc')
                      ->get();
           $rests = json_decode($categories, true);   
           $services = services::all()->toArray();
           return view('manage_category', compact('services','rests'));
    }


    public function store(Request $request)
    {
        
        if($request->file('image'))
           {
               $this->validate($request, [
                    'image'             =>      'required|file|mimes:jpeg,jpg,png,gif|required|max:10000'
                ]);
               $image = ltrim(Storage::put('public/image', $request->file('image')), 'public'); 
           }else{
               $image = ""; 
           }

        $categorys = new Categorys([
            'sub_category_name'          =>   $request->category_name,
            'sub_category_time_limit'    =>   $request->time,
            'sub_category_image'         =>   "http://temp.pickzy.com/samarnas/storage/app/public".$image,
            'service_id'                 =>   $request->service_name
        ]);
       
        $categorys->save();
        return back()->with('success', 'Category Inserted');
    
    }
    
    public function view(Request $request)
    {        
        $id = $request->input('sub_category_id'); //print_r($id); exit();
        $category = categorys::where('sub_category_id',$id)->get()->toArray(); 
        $service_id = $category[0]['service_id'];
        $services = services::where('service_id',$service_id)->get()->toArray();  // print_r($services); exit();
        foreach ($category as $key => $categoryview) {
            // print_r($categoryview['sub_category_id']); exit();
         } 
       return view('category_view', compact('categoryview','services'));
    }

    public function show(Request $request)
    {   
        $id = $request->input('sub_category_id'); 
        $category = categorys::where('sub_category_id',$id)->get()->toArray(); // print_r($category); exit();
        $service_id = $category[0]['service_id'];
        $services = services::where('service_id',$service_id)->get()->toArray();  // print_r($services); exit();
        foreach ($category as $key => $cat_value) {
            
         } 
        $services = services::all()->toArray();
        return view('category_edit', compact('cat_value','services','service_id'));
    }
   

    public function update(Request $request)
    {
 
        if($request->file('image'))
           {
               $this->validate($request, [
                    'image'             =>      'required|file|mimes:jpeg,jpg,png,gif|required|max:10000'
                ]);
                $image = ltrim(Storage::put('public/image', $request->file('image')), 'public');  
                $image = "http://temp.pickzy.com/samarnas/storage/app/public".$image;
               
           }else{
                $image = $request->get('old_img'); 
           } 
          //  print_r($request->id); exit();
        categorys::where('sub_category_id',$request->id)->update(['sub_category_name'=> $request->category_name,'service_id'=>$request->service_id,'sub_category_image'=>$image,'sub_category_time_limit'=>$request->time]);
        return back()->with('success', 'Category Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {  
        $id = $request->input('sub_category_id');
        // Services::where('service_id', $id)->delete();
        DB::table('categorys')
            ->where('sub_category_id','=',$id)
            ->delete();
        return back()->with('success', 'Category Deleted');
    }
   
}
