<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Validator;

class StandardController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth', [
            'except' => [
                'getAll','getStandardandSubjects'

            ],
        ]);
    }


    public function add(Request $request)
    {
       
        if (!empty($request->post('data')[0]['standard_name'])) {

            foreach ($request->post('data') as $key => $value) {

                if (DB::table('standards')->where('standard_name', $value['standard_name'])->count() == 0) {
                    DB::table('standards')->insert(['standard_name' =>$value['standard_name'],'description'=>$value['description'],
                    'country_code'=>$request->post('code')['country_code']]);
                } else {
                    //data exists

                    return response()->json([
                        'status' => false,
                        'message' => sprintf('%s is already taken.', $value['standard_name'])
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

    public function getStandardandSubjects(Request $request){
        
        
         $categroies = array();
        $sub_categroies = array();
        $sub = array();
        $child = array();
        $res = array();
       
         $validator = Validator::make($request->all(), [
            'country_code'           => 'required'
        ]);
        
        if ($validator->fails()) {
            return $this->formatErrorResponse($validator);
        }
         $country_code=$request->post('country_code');
        try {
            $data = DB::table('standards as s')->where('s.country_code',$country_code)->select('id','standard_name as name','description')->get();
          
            if(count($data)>0){
                $i = 0;
                foreach($data as $std){
                     $categroies = array('standard_name' => $std->name,'id'=>$std->id,'description'=>$std->description);
                     $subjects=DB::table('subjects')->where('standard_id',$std->id)->select('id','subject_name')->get();
                  
                     foreach ($subjects as $key => $value) {
                          
                            $score=DB::table('subcategory')
                            ->where('subject_id',$value->id)
                            ->where('standard_id',$std->id)
                            ->where('country_code',$country_code)->count();
                                           
                        $value->count=$score;
                        $sub['subjects'][] = $value;
                    }
                    
                     $sub1['subjects'] = $sub;
                    $res['standards'][$i] = array_merge($categroies, $sub1);
                     $i++;
                }
            }else{
                return response()->json(['status' => false, 'data' =>[]], 200);
            }
            return response()->json(['status' => true, 'data' =>$res], 200);
        } catch (\Exception $e) {
 
           
            return response()->json(['status' => false, 'message' =>json_encode($e)], 200);
        }
    }

    public function getAll(Request $request)
    {
        $country_code=$request->post('country_code');
        try {
            $data = DB::table('standards as s')->join('countries as c','c.id','=','s.country_code')
            ->where('s.country_code',$country_code)
            ->select('s.id','s.standard_name','s.description','c.id as country_id')->orderBy('s.id','desc')->get();
            
            if(count($data)>0){

                return response()->json(['status' => true, 'data' => $data], 200);
            }else{
                return response()->json(['status' => false, 'data' =>[]], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' =>$e], 200);
        }
    }

    public function delete_subject($id)
    {
        DB::table('standards')->where('id', $id)->delete();
        return response()->json(['status' => true, 'message' => 'Standard Deleted Successfully'], 200);
    }

    public function edit($id)
    {
        return response()->json(['status' => true, 'data' => DB::table('standards')->where('id', $id)->select('id', 'standard_name')->first()], 200);
    }
    public function getStandardsByCountryId(Request $request){
       
         return response()->json(['status' => true, 'data' => DB::table('standards')->where('country_code',$request->post('country_code'))->select('id','standard_name')->get()], 200);
       
        
    }
    public function update(Request $request)
    {
        $data = $request->post();
        $id = $request->post('id');
        DB::table('standards')->where('id', $id)->update(['standard_name' => $data[0]['standard_name
        ']]);
        return response()->json(['status' => true, 'message' => 'Successfully Updated'], 200);
    }
}
