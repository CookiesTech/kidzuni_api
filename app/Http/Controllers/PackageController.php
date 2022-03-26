<?php

namespace App\Http\Controllers;

use  App\Models\Teachers;
//use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DB;
use Validator;

class PackageController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth',['except'=>
        [
            'getAll','getPackage'
    ],
    ]);
    }


    public function add(Request $request)
    {       

        if (!empty($request->post('data')['type']) && $request->post('data')['package_for']) {           
               
                   if($request->post('data')['package_for']=='parent'){
                    //check package already esists in school type
                        if (DB::table('packages')->where('type', $request->post('data')['type'])->where('package_for', $request->post('data')['package_for'])->count()==0) 
                            {
                                 DB::table('packages')->insert(['type' => $request->post('data')['type'],'package_for'=>$request->post('data')['package_for'],
                                'price'=>$request->post('data')['price'],'additional_price'=>$request->post('data')['additional_price']]);
                            }#same packeage exists
                            else{
                                 return response()->json([
                            'status' => false,
                            'message' => sprintf('%s This Package Type is already taken.', $request->post('data')['type'])
                        ], 200);
                            }
                       
                   }else{
                    //check package already esists in school type
                        if (DB::table('packages')->where('type', $request->post('data')['type'])->where('package_for', 'school')->where('minimum_count',$request->post('data')['student_min_count'])->count()==0) 
                        {
                            DB::table('packages')->insert(['type' => $request->post('data')['type'],
                            'price'=>$request->post('data')['price'],'package_for'=>$request->post('data')['package_for'],
                            'minimum_count'=>$request->post('data')['student_min_count'],'maximum_count'=>$request->post('data')['student_max_count']]);
                        }
                        #package exists
                        else{
                            return response()->json([
                            'status' => false,
                            'message' => sprintf('%s This Package Type is already taken.', $request->post('data')['type'])
                        ], 200);
                        }
                   }
            return response()->json([
                'status' => true,
                'message' => 'Successfully Added'
            ], 200);
        }
        //no data
        else {
            return response()->json(['status' => false, 'message' => 'Input Data Cannot be Empty'], 200);
        }
    }

    public function getAll(Request $request)
    {
       
        $data=DB::table('packages')->get();

        return response()->json(['status' => true, 'data' => $data], 200);
        
    }

    public function getPackage(Request $request){
        $validator = Validator::make($request->all(), [
            'package_for'           => 'required',
            'type'=>'required'
        ]);

        if ($validator->fails()) {
            return $this->formatErrorResponse($validator);
        }

        $data=DB::table('packages')->where('package_for',$request->post('package_for'))->where('type',$request->post('type'))->get();
        if($data){
            return response()->json(['status' => true, 'data' => $data], 200);
        }
        else{
            return response()->json(['status' => false, 'message' =>'No Package found  for this !'], 200);
        }
    }

    public function delete_subject($id)
    {
        DB::table('subjects')->where('id', $id)->delete();
        return response()->json(['status' => true, 'message' => 'Subject Deleted Successfully'], 200);
    }

    public function edit($id)
    {
        return response()->json(['status' => true, 'data' => DB::table('subjects')->where('id', $id)->select('id', 'standard', 'subject_name')->first()], 200);
    }

    public function update(Request $request)
    {
        $data = $request->post();

        $id = $request->post('id');
        DB::table('subjects')->where('id', $id)->update(['standard' => $data[0]['standard'], 'subject_name' => $data[0]['subject_name']]);
        return response()->json(['status' => true, 'message' => 'Successfully Updated'], 200);
    }
}
