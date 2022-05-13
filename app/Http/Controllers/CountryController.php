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
                        'code'=>$value['code'],
                        'currency'=>$value['currency']
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

   
}
