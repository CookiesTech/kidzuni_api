<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Validator;

class SubCategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth',['except'=>
            [
                'getSubCategoryBymainCategory','search_results'
            ],
        ]);
    }


    public function add(Request $request)
    {

        if (!empty($request->post('data')[0]['category_name'])) {

            foreach ($request->post('data') as $key => $value) {

                if (DB::table('subcategory')->where('name', $value['category_name'])->where('mc_id', $request->post('mc_id'))->where('subject_id', $request->post('subject_id'))->where('country_code', $request->post('country_code'))->count() == 0) {
                    DB::table('subcategory')->insert(['name' => $value['category_name'], 'mc_id' => $request->post('mc_id'), 'subject_id' => $request->post('subject_id'),'country_code'=>$request->post('country_code')]);
                } else {
                    //data exists

                    return response()->json([
                        'status' => false,
                        'message' => sprintf('%s is already taken.', $value['category_name'])
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
            $data = DB::table('subcategory as s')->join('maincategory as m', 'm.id', '=', 's.mc_id')
            ->join('standards as std','std.id','=','m.standard_id')
            ->join('countries as c','c.id','=','std.country_code')
            ->select('s.name', 's.id', 'm.name as mcname','std.standard_name','c.image')->orderBy('s.id','desc')
            ->get();

            return response()->json(['status' => true, 'data' => $data], 200);
        } catch (\Exception $e) {

            return response()->json(['status' => false, 'data' => []], 200);
        }
    }

    public function delete($id)
    {
        DB::table('subcategory')->where('id', $id)->delete();
        return response()->json(['status' => true, 'message' => 'Category Deleted Successfully'], 200);
    }

    public function edit($id)
    {
        return response()->json(['status' => true, 'data' => DB::table('standards')->where('id', $id)->select('id', 'standard_name')->first()], 200);
    }

    public function update(Request $request)
    {
        $data = $request->post();

        $id = $request->post('id');
        DB::table('standards')->where('id', $id)->update(['standard_name' => $data[0]['standard_name
        ']]);
        return response()->json(['status' => true, 'message' => 'Successfully Updated'], 200);
    }

    public function getSubCategoryBymainCategory(Request $request)
    {
        $data=DB::table('subcategory')->where('mc_id', $request->post('mc_id'))->select('id','name')->get();
        return response()->json(['status' => true, 'data' =>$data], 200);
    }

    public function search_results(Request $request){
        $final_data=[];
        $text=$request->search_text;
        if(!empty($text))
        {
            $country_code=$request->country_code;
                $data=DB::table('standards')->where('country_code',$country_code)->select('id','standard_name')->get();
                    
                if($data){
                    foreach($data as $val)
                    {               
                        $search_data=DB::table('questions as q')
                                ->Where('q.subcategory', 'like', '%' . $text . '%')->Where('q.country_code',$country_code)
                                ->Where('q.standard_id',$val->id)
                                ->select('q.id','q.subcategory')->get();
                        if(count($search_data)>0)
                        {
                            $final_data[]=array('class_name'=>$val->standard_name,'sub_topics'=>$search_data);
                        }
                    }
                }
                if($final_data){
                     return response()->json(['status' => true, 'data' =>$final_data], 200);
                }else{
                    return response()->json(['status' => false, 'data' =>[]], 200);

                }
        }else{
            return response()->json(['status' => false, 'messsage' =>'search text empty'], 200);
        }
        
    }
}
