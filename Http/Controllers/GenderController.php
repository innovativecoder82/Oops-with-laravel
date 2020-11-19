<?php

namespace samarnas\Http\Controllers;
use samarnas\Gender_types;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GenderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $gender = gender_types::all()->toArray();
        return view('manage_gender', compact('gender'));

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
        $this->validate($request, [
            'image'             =>      'required|file|mimes:jpeg,jpg,png,gif|required|max:10000'
        ]);

        if($request->file('image'))
           {
               $image = ltrim(Storage::put('public/gender_image', $request->file('image')), 'public'); 
           }else{
               $image = ""; 
           }  //print_r($image); exit();

        $gender = new gender_types([
            'gender_type_name'    =>      $request->gender,
            'gender_type_image'   =>      "http://temp.pickzy.com/samarnas/storage/app/public".$image,
        ]); 
        // print_r("<pre>");
        // print_r($gender);
        // print_r("</pre>");
        // exit();
       
        $gender->save();
        return back()->with('success', 'Gender Inserted');
    
    }

     public function view(Request $request)
        {
            // print_r($request); exit();
            $id = $request->type_id; //print_r($id); exit();
            $gender = gender_types::where('type_id',$id)->get()->toArray();
            foreach ($gender as $key => $genView) {
                
             }  
            return view('genderView', compact('genView'));
        }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {  
        //dd($request); exit();
        $id = $request->input('type_id'); //print_r($id); exit();
        $gender = gender_types::where('type_id',$id)->get()->toArray(); 
        foreach ($gender as $key => $gen) {
            
         } 
        return view('genderEdit', compact('gen'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //print_r($request->type_id); exit();
        $this->validate($request, [
            'image'             =>      'required|file|mimes:jpeg,jpg,png,gif|required|max:10000'
        ]);
        
        if($request->file('image'))
           {
                $image = ltrim(Storage::put('public/gender_image', $request->file('image')), 'public');  
                $image = "http://temp.pickzy.com/samarnas/storage/app/public".$image;
           }else{
               $image = $request->get('old_img'); 
           }
        gender_types::where('type_id',$request->id)->update(['gender_type_name'=> $request->gender_type_name,'gender_type_image'=>$image]);
        return back()->with('success', 'Gender Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        gender_types::where('type_id',$request->type_id)->delete(); //print_r($rests); exit();
        return back()->with('success', 'Gender Deleted');
    }

    public function getGender(Request $request)
    {
            $gender = Gender_types::where('type_id','!=','')->get();
            return response()->json([
                        'message' => "List of Gender.",
                        'gender' => $gender,
                        'status' => true,
                        'status_code' => 200
                    ]);

    }

    
}
