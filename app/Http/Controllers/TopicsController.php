<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Validator;

class TopicsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth',[
            'except'=>['getTopics'],
        ]);
    }


    public function getTopics(Request $request)
    {
        $categroies = array();
        $sub_categroies = array();
        $sub = array();
        $child = array();
        $res = array();
        $res['status'] = true;
         $validator = Validator::make($request->all(), [
            'standard_id'           => 'required'
        ]);

        if ($validator->fails()) {
            return $this->formatErrorResponse($validator);
        }
        try {
            $data = DB::table('maincategory as m')->where('m.standard_id',$request->post('standard_id'))->select('id','name')->get();
            if($data){
                $i = 0;
                foreach($data as $maintopics){
                     $categroies = array('main_topic' => $maintopics->name);
                     $sub['sub_topics']=DB::table('subcategory')->where('mc_id',$maintopics->id)->select('id','name')->get();
                     $sub['sub_topics'] = $sub['sub_topics'];
                    $res['Topics'][$i] = array_merge($categroies, $sub);
                     $i++;
                }
            }else{
                return response()->json(['status' => false, 'message' =>'No data found'], 200);
            }
            return response()->json($res, 200);
        } catch (\Exception $e) {

            return response()->json(['status' => false, 'data' => []], 200);
        }
    }

}
