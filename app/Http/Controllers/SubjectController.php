<?php

namespace App\Http\Controllers;

use  App\Models\Teachers;
//use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DB;
use Validator;

class SubjectController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth',['except'=>
        [
            'getAll',
    ],
    ]);
    }


    public function add(Request $request)
    {

        if (!empty($request->post('data')[0]['subject_name'])) {

            foreach ($request->post('data') as $key => $value) {

                if (DB::table('subjects')->where('subject_name', $value['subject_name'])->where('country_code', $request->post('code')['country_id'])->where('standard_id', $request->post('standard')['standard_id'])->count() == 0) {
                    DB::table('subjects')->insert(['subject_name' => $value['subject_name'],'country_code' => $request->post('code')['country_id'],'standard_id' => $request->post('standard')['standard_id']]);
                } else {
                    //data exists

                    return response()->json([
                        'status' => false,
                        'message' => sprintf('%s is already taken.', $value['subject_name'])
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
        $country_code=$request->post('country_code');
        $final_data=[];$temp=[];
       
        try {
            $data = DB::table('subjects')->select('id','subject_name')->get();
            if($data){
                foreach($data as $sub){
                    $skills_count=DB::table('subcategory')->where('subject_id',$sub->id)->where('country_code',$country_code)->count();
                    $sub->skills_count=$skills_count;
                    $temp=array_push($final_data,$sub);
                }
            }

            return response()->json(['status' => true, 'data' => $final_data], 200);
        } catch (\Exception $e) {

            return response()->json(['status' => false, 'data' => []], 404);
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
