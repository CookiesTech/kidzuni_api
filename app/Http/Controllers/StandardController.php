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

                if (DB::table('standards')->where('standard_name', $value['standard_name'])->where('country_code',$request->post('code')['country_code'])->count() == 0) {
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
          $res['standards'] = array();       
       $subTopics=[]; 
            $data = DB::table('standards as s')            
                    ->where('s.country_code',$request->post('country_code'))     
                    ->select('s.id','s.standard_name as name','description')->get();
            
      
            if(count($data)>0)
            {
                $score=0;
                foreach($data as $std_obj)
                {
                    $subjects=DB::table('subjects as sc')
                                ->where('sc.standard_id',$std_obj->id)
                                ->where('sc.country_code',$request->post('country_code'))
                                ->groupBy('sc.subject_name','sc.id')
                                ->select('sc.id','sc.subject_name')
                                ->get();
                      
                    if(count($subjects)>0){
                         $res['standards'][]=array('standard_name'=>$std_obj->name, 'id'=>$std_obj->id,'description'=>$std_obj->description,
                'subjects'=>$subjects);
                    }
               
                               
                } #maintopic lop end
               foreach ($res['standards'] as $key1 => $value) {
                 
                  foreach ($value['subjects'] as $key => $subtopics) {
                    
                          $getcount=DB::table('subcategory')
                                ->where('country_code',$request->post('country_code'))
                                    ->where('subject_id',$subtopics->id)
                                    ->where('standard_id',$value['id'])
                                    ->groupBy('subject_id')
                                    ->count();
                           $subtopics->count=$getcount;

                  }
               }


            }
            else{
                return response()->json(['status' => false, 'data' =>[]], 200);
            }
            return response()->json(['status' => true, 'data' =>$res], 200);
       
    }
    

    public function getAll(Request $request)
    {
        $country_code=$request->post('country_code');
        
        try {
            $data = DB::table('standards as s')
                    ->where('s.country_code',$country_code)
                    ->select('s.id','s.standard_name')
                    ->orderBy('s.id','desc')
                   
                    ->get();
            
            if(count($data)>0){

                return response()->json(['status' => true, 'data' => $data], 200);
            }else{
                return response()->json(['status' => false, 'data' =>[]], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' =>$e], 200);
        }
    }

    public function standard_list(Request $request)
    {
        try {
            $data = DB::table('standards as s')
            ->join('countries as c','c.id','=','s.country_code')
                    ->select('s.id','s.standard_name','c.image','c.code as country_code')
                    ->orderBy('s.id','desc')
                   
                    ->get();
            
            if(count($data)>0){

                return response()->json(['status' => true, 'data' => $data], 200);
            }else{
                return response()->json(['status' => false, 'data' =>[]], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' =>$e], 200);
        }
    }

    public function delete_standard($id)
    {
        DB::table('standards')->where('id', $id)->delete();
        DB::table('maincategory')->where('standard_id', $id)->delete();
        DB::table('subcategory')->where('standard_id', $id)->delete();
        DB::table('questions')->where('standard_id', $id)->delete();
        DB::table('test_history')->where('standard_id', $id)->delete();
        DB::table('subjects')->where('standard_id', $id)->delete();
        DB::table('scores')->where('standard_id', $id)->delete();
        DB::table('teacher_sub_mapping')->where('standard_id', $id)->delete();
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
        DB::table('standards')->where('id', $id)->update(['standard_name' => $data['standard_name']]);
        return response()->json(['status' => true, 'message' => 'Successfully Updated'], 200);
    }
}
