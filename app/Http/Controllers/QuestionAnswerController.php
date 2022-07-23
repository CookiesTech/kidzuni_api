<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Validator;

class QuestionAnswerController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function add(Request $request)
    {
        DB::table('question_answer')->insert(['user_id'=>$request['user_id'],'question'=>$request->question]);
        return response()->json([
                'status' => true,
                'message' => 'Successfully Added'
            ], 200);
    }

    public function getAll()
    {
       $data=DB::table('question_answer')->orderBy('id','desc')->get();
        return response()->json([
                'status' => true,
                'data' =>$data
            ], 200);
    }
    
    public function get_teacher_questions(Request $request){

       $data=DB::table('question_answer')->where('user_id',$request['user_id'])->orderBy('id','desc')->get();
        return response()->json([
                'status' => true,
                'data' =>$data
            ], 200);
       
    }

    public function update_answer(Request $request)
    {
        DB::table('question_answer')->where('id',$request->id)->update(['answer'=>$request->answer]);
        return response()->json([
                'status' => true,
                'message' => 'Successfully Updated'
            ], 200);
    }

}
