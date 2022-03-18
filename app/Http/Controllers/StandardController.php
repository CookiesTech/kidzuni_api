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
                'getAll',

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

    public function getAll()
    {
        try {
            $data = DB::table('standards as s')->join('countries as c','c.id','=','s.country_code')->
            select('s.id','s.standard_name','c.name as country_code','c.image')->orderBy('s.id','desc')->get();

            return response()->json(['status' => true, 'data' => $data], 200);
        } catch (\Exception $e) {

            return response()->json(['status' => false, 'data' => []], 200);
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
