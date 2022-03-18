<?php

namespace App\Http\Controllers;

use  App\Models\Teachers;
//use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DB;
use Validator;

class CountryController extends Controller
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
      
         if (!empty($request->post('data')[0]['name'])) {
                foreach ($request->post('data') as $key => $value) {
                    DB::table('countries')->insert([
                        'name' => $value['name'],
                        'image'=>$value['image'],
                        'code'=>$value['code']
                    ]);
                }
            }
            
            return response()->json([
                'status' => true,
                'message' => 'Successfully Added'
            ], 200);
        
    }

    public function getAll()
    {
        try {
            $data = DB::table('countries')->get();

            return response()->json(['status' => true, 'data' => $data], 200);
        } catch (\Exception $e) {

            return response()->json(['status' => false, 'data' => []], 200);
        }
    }

    public function delete_country($id)
    {
        if(DB::table('questions')->where('country_code', $id)->count()==0){
            DB::table('countries')->where('id', $id)->delete();
            return response()->json(['status' => true, 'message' => 'Country Deleted Successfully'], 200);
        }else{
             return response()->json(['status' => false, 'message' => 'Country Record Already exists in questions Table'], 200);
        }
        
    }

    public function edit($id)
    {
        return response()->json(['status' => true, 'data' => DB::table('countries')->where('id', $id)->select('id', 'standard', 'subject_name')->first()], 200);
    }

    public function update(Request $request)
    {
        $data = $request->post();

        $id = $request->post('id');
        DB::table('countries')->where('id', $id)->update(['standard' => $data[0]['standard'], 'subject_name' => $data[0]['subject_name']]);
        return response()->json(['status' => true, 'message' => 'Successfully Updated'], 200);
    }
}
