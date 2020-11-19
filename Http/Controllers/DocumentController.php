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

class DocumentController extends Controller
{
    public function index()
    {
       $customer = User::where('user_type','=','1')->get()->toArray(); 
       $provider = User::where('user_type','!=','1')->get()->toArray(); 
       foreach($provider as $prov => $provider1)
       {
           $doc_count   = DB::table('provider_document')->where('provider_id','=',$provider1['id'])->where('document_type','=',"id_proof")->get()->toArray(); 
           $doc_count   = json_decode(json_encode($doc_count),true);
           
           /*ID Proof Approvde or Pending*/
           foreach($doc_count as $key => $value)
           {
              $data[] = $value['document_status'];
           }
           $data = array_unique($data);
           $countData = count($data);
        //   dd($prov);
           if($countData == 1)
           {
                $provider[$prov]['doc_status']   = $data[0];
           }
           else
           {
               $provider[$prov]['doc_status']   = "Partially Pending";
           }
           
           $data = array();
            
           /*End ID Proof Approvde or Pending*/
           
           $doc_count1  = count($doc_count);
           $provider[$prov]['doc_count'] = $doc_count1;
           $provider[$prov]['doc_details'] = $doc_count;
           
           $doc_count1   = DB::table('provider_document')->where('provider_id','=',$provider1['id'])->where('document_type','=',"experience_doc")->get()->toArray();
           $doc_count1   = json_decode(json_encode($doc_count1),true);
           
           /*Experiance Document Approvde or Pending*/
           foreach($doc_count1 as $key => $value)
           {
              $data[] = $value['document_status'];
               
           }
           $data = array_unique($data);
           $countData = count($data);
           if($countData == 1)
           {
                $provider[$prov]['doc_status1']   = $data[0];
           }
           else
           {
               $provider[$prov]['doc_status1']   = "Partially Pending";
           }
           $data = array();
           /*End Experiance Document Approvde or Pending*/
           
           $doc_count11  = count($doc_count1);
           $provider1[$prov]['doc_count1'] = $doc_count11;
           $provider1[$prov]['doc_details1'] = $doc_count1; 
           $provider[$prov]['doc_count1'] = $doc_count11;
           $provider[$prov]['doc_details1'] = $doc_count1;
           
           $extras   = DB::table('extras')->where('provider_id','=',$provider1['id'])->get()->toArray(); 
           $extras = json_decode(json_encode($extras), true);
           $extras1  = count($extras);
           $provider[$prov]['doc_count2'] = $extras1;
           $provider[$prov]['doc_details2'] = $extras;
       }
            /*print_r("<pre>");
            print_r($provider);
            print_r("</pre>");
            exit();*/
       return view('manage_document', compact('customer','provider','provider1','extras'));
    }
    
    /*-----------------Customer Document-------------------*/
     
    public function customerId(Request $request)
    {
        $id = $request->id; 
        $customer = User::where('id',$id)->get()->toArray(); 
        foreach ($customer as $key => $value) {
          
        } 
        return view('custIddoc', compact('value'));
    }
     
    public function custView(Request $request)
    {
        $id = $request->id; 
        $customer = User::where('id',$id)->get()->toArray(); 
        foreach ($customer as $key => $value) {
          
        } 
        return view('custDocumentView', compact('value'));
    }
    
    public function custDestroy(Request $request)
    {
        User::where('id',$request->id)->delete(); //print_r($rests); exit();
        return back()->with('success', 'Customer Deleted Successfully');
    }
    
   public function docApprove(Request $request)
    {
        $id = $request->id;
        $User = User::find($id);
        $User->id_proof_status_for_user = "approved";
        $User->save();
        return back()->with('success', 'Customer Document Approved');
    }
    
    public function cust_docReject(Request $request)
    {
        $id = $request->id; // print_r($id); exit();
        $reject = $request->cust_reject; 
        User::where('id','=',$id)->update(['id_proof_reject_for_user' => $reject, 'id_proof_status_for_user' =>'rejected']);
        return back()->with('success', 'Customer Document Rejected');
    }
    
    /*-----------------Provider Document-------------------*/
    
    public function provView(Request $request)
    {
        $id = $request->id; 
        $provider = User::where('id',$id)->get()->toArray(); 
        foreach ($provider as $key => $value) {
          
        } 
        return view('provDocumentView', compact('value'));
    }
    
    public function provDestroy(Request $request)
    {
        User::where('id',$request->id)->delete(); //print_r($rests); exit();
        return back()->with('success', 'Provider Deleted Successfully');
    }
    
    public function iddocApprove(Request $request)
    {
        $id = $request->id;  // print_r($id); exit();
        $doc_count = DB::table('provider_document')->where('document_id','=',$id)->where('document_type','=',"id_proof")->update(['document_status' => "approved"]);
        return back()->with('success', 'Provider Document Approved');
    }
    
    public function prov_docReject(Request $request)
    {
        $id = $request->id; // print_r($id); exit();
        $reject = $request->prov_reject; 
        $doc_count = DB::table('provider_document')->where('document_id','=',$id)->where('document_type','=',"id_proof")->update(['id_proof_reject_for_prov' => $reject,'document_status'=>'rejected']);
        return back()->with('success', 'Provider Document Rejected');
    }
    
    public function expdocApprove(Request $request)
    {
        $id = $request->id; // print_r($id); exit();
        $doc_count = DB::table('provider_document')->where('document_id','=',$id)->where('document_type','=',"experience_doc")->update(['document_status' => "approved"]);
        return back()->with('success', 'Provider Document Approved');
    }
    
    public function prov_expdocReject(Request $request)
    {
        $id = $request->id; // print_r($id); exit();
        $reject = $request->prov_reject; 
        $doc_count = DB::table('provider_document')->where('document_id','=',$id)->where('document_type','=',"experience_doc")->update(['exp_proof_reject_for_prov' => $reject,'document_status'=>'rejected']);
        return back()->with('success', 'Provider Document Rejected');
    }
    
    public function businessApprove(Request $request)
    {
        $id = $request->id; // print_r($id); exit();
        $doc_count = DB::table('extras')->where('extras_id','=',$id)->update(['bussiness_license_approve' => "approved"]);
        return back()->with('success', 'Provider Document Approved');
    }
    
    public function businessReject(Request $request)
    {
        $id = $request->id; // print_r($id); exit();
        $reject = $request->busi_reject; 
        Extras::where('extras_id','=',$id)->update(['bussiness_license_reject' => $reject, 'bussiness_license_approve' => "rejected"]);
        return back()->with('success', 'Provider Document Rejected');
    }

}
