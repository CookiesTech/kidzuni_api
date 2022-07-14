<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Illuminate\Support\Facades\Auth;
class QuestionController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth',[
            'except'=>['upload_question']
        ]);
    }

    public function insert_quiztestdata(Request $request)
    {
        
        $student_id=$request['user_id'];
        $subcategory_id=$request->post('subcategory_id');
       
       
            DB::table('test_history')->insert([
            'question_id'=>$request->post('question_id'),
            'student_id'=>$student_id,
            'subcategory_id'=>$subcategory_id,
            'standard_id'=>$request->post('standard_id'),
            'subject_id'=>$request->post('subject_id'),
            'correct_answer'=>$request->post('correct_answer'),
            'student_answer'=>$request->post('student_answer')
            ]);
        
            DB::table('scores')->where('student_id',$student_id)->where('subcategory_id',$subcategory_id)->delete();
            
            DB::table('scores')->insert([
                'student_id'=>$student_id,
                'subcategory_id'=>$subcategory_id,
                'score'=>$request->post('score'),
                'standard_id'=>$request->post('standard_id'),
                'subject_id'=>$request->post('subject_id'),
                'time_spent'=>$request->post('time')
            ]);
             $score=DB::table('scores')->where('student_id',$student_id)->where('subcategory_id',$subcategory_id)->pluck('score');
            return response()->json(['status' => true, 'score' =>$score], 200);
        
      
        
    }

    public function getAll()
    {
        try {
            $data = DB::table('questions')->orderBy('id','desc')->get();

            return response()->json(['status' => true, 'data' => $data], 200);
        } catch (\Exception $e) {

            return response()->json(['status' => false, 'data' => []], 200);
        }
    }

    public function getQuestionsByID(Request $request)
    
    {
         $subcategory_id=$request->post('subcategory_id');
         $get_existing_attnd_question=DB::table('test_history')
                ->whereRaw('correct_answer = student_answer')
                ->where('student_id',$request['user_id'])
                ->where('subcategory_id',$subcategory_id)
                ->pluck('question_id');
          $res=json_decode(json_encode($get_existing_attnd_question,true));
         $get_existing_attnd_question=$res;
        #check student only logged or not
        if($request['role']==5){
            
            try {
                $data=[];
                #check whethet the student attended test for same topic or not
               if($get_existing_attnd_question){
                     $data = DB::table('questions')->where('subcategory_id',$subcategory_id)
                     ->whereNotIn('id',$get_existing_attnd_question)
                     ->inRandomOrder()
                     ->get();
                    
               }
               else{
                    $data = DB::table('questions')->where('subcategory_id',$subcategory_id)->inRandomOrder()->get();
               }
           
                $score=DB::table('scores')->where('subcategory_id',$subcategory_id)->where('student_id',$request['user_id'])->sum('score');
              
                if($data){
                     return response()->json(['status' => true, 'data' => $data,'score'=>$score], 200);
               }
               #no New Questions Found he attend all
               else{
                    return response()->json(['status' => false], 200);
               }
            } catch (\Exception $e) {

                return response()->json(['status' => false], 200);
            }
        }
        else{
            return response()->json(['status' => false, 'message' =>'unAuthorized'], 200);
        }
    }


    public function getTestResults(Request $request){
        $student_id=$request['user_id'];
        $subcategory_id=$request->post('subcategory_id');
        $subcategory_name=DB::table('subcategory')->where('id',$subcategory_id)->select('name')->first();
        $score_details=DB::table('scores')->where('student_id',$student_id)
                        ->where('subcategory_id',$subcategory_id)
                        ->select('score','time_spent')->first();
       $question_count=DB::table('test_history')->where('subcategory_id',$subcategory_id)->where('student_id',$student_id)->count();
    if($score_details)
     return response()->json(['status' => true, 'data' =>$score_details,'question_count'=>$question_count], 200);
     else
      return response()->json(['status' => false, 'data' =>[]], 200);
    }
     

    public function upload_question(Request $request)
    {

        $arr_file = explode('.', $_FILES['file']['name']);
     
        $extension = end($arr_file);
        if ('csv' == $extension) {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        } else {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }
        try {
   
            $spreadsheet = $reader->load($_FILES['file']['tmp_name']);
            $sheet        = $spreadsheet->getActiveSheet();
            $tota_row    = $sheet->getHighestDataRow();
            $column_limit = $sheet->getHighestDataColumn();

            $row_range    = range(2, $tota_row);
            $column_range = range('F', $column_limit);
            $startcount = 2;
            $data = array();
            $headers   = $sheet->rangeToArray('A1' . ':' . $column_limit . '1', null, true, false)[0];
            $totalQuestionsAdded = 0;
            $failedToImport     = [];

            // if ($missingHeaders = array_diff(['first_name', 'last_name', 'email', 'mobile', 'role_id', 'profile_picture', 'department_id', 'location_id', 'designation', 'username'], $headers)) {
            //     return response()->json([
            //         'success' => false,
            //         'result'  => ['file' => sprintf('Please choose valid file. Following %s missing <b>%s</b>', count($missingHeaders) > 1 ? 'headers are' : 'header is', implode(',', $missingHeaders))],
            //     ]);
            // }
            if ($tota_row > 1) {
                 for ($row = 2; $row <= $tota_row; $row++) {
                      $subject_id = $sheet->getCell('A' . $row)->getValue();
                      $standard = $sheet->getCell('B' . $row)->getValue();
                      $subcategory = $sheet->getCell('C' . $row)->getValue();
                      $country_code = $sheet->getCell('D' . $row)->getValue();
                      $question_text = $sheet->getCell('E' . $row)->getValue();
                      $question_image = $sheet->getCell('F' . $row)->getValue();
                      $option1 = $sheet->getCell('G' . $row)->getValue();
                      $option2 = $sheet->getCell('H' . $row)->getValue();
                      $option3 = $sheet->getCell('I' . $row)->getValue();
                      $option4 = $sheet->getCell('J' . $row)->getValue();
                      $answer = $sheet->getCell('K' . $row)->getValue();
                      $solution = $sheet->getCell('L' . $row)->getValue();
                    //   $mark = $sheet->getCell('L' . $row)->getValue();
                    //   $wrong_answermark_deduction = $sheet->getCell('M' . $row)
                  

                    $errorMessage = [];
                    if (empty($errorMessage) &&    empty($standard)) {
                        
                        $errorMessage[] = 'Standard name is required';
                    }
                    if (empty($errorMessage) &&    empty($subcategory)) {
                        $errorMessage[] = 'Subcategory name is required';
                    }
                   
                     if (DB::table('subcategory')->where('name', $subcategory)->count()==0) {
                        $errorMessage[] = sprintf('%s is Not Available. Check with superadmin', $subcategory);
                    }

                    if (!empty($errorMessage)) {
                        $dataToAdd['errors'] = implode('<br>', $errorMessage);
                        $failedToImport[]    = $dataToAdd;
                    } else {
                         $country=DB::table('countries')->where('name', $country_code)->select('id')->first();
                        $subcategory_id=DB::table('subcategory')->where('name', $subcategory)->select('id')->first();
                      
                       
                         $subject=DB::table('subjects')->where('subject_name', ucfirst($subject_id))
                         ->where('country_code', $country->id)
                         ->pluck('id');

                          $standard_id=DB::table('standards')
                        ->where('country_code', $country->id)
                         ->where('standard_name', $standard)
                        ->select('id')->first();

                        DB::table('questions')->insert([
                            'subject_id'=>$subject[0],
                            'standard_id'=>$standard_id->id,
                            'subcategory'=>$subcategory,
                            'subcategory_id'=>$subcategory_id->id,
                            'country_code'=>$country->id,
                            'question_image'=>$question_image,
                            'question_text'=>$question_text,
                            'option1'=>$option1,
                            'option2'=>$option2,
                            'option3'=>$option3,
                            'option4'=>$option4,
                            'answer'=>$answer,
                            'solution'=>$solution,
                            //'mark'=>$mark,
                           // 'wrong_answer_mark'=>$wrong_answermark_deduction                                 
                        ]);
                             $totalQuestionsAdded++;
                    }

                 }


            } else {
                return response()->json([
                    'status'       => false,
                    'message'      => 'Add minimum one row data',
                ], 200);
            }

          return response()->json([
                'status'               =>true,
                'failed_to_import'      => $failedToImport,
                'successfully_imported' => $totalQuestionsAdded,
            ], 200);   
        } catch (Exception $e) {
            $error_code = $e->errorInfo[1];
            return back()->withErrors('There was a problem uploading the data!');
        }
    }

    public function insert_question(Request $request){
       
        $subcategory_name=DB::table('subcategory')->where('id', $request->post('subcategory_id'))->first('name');
       DB::table('questions')->insert([
        'answer'=>$request->post('answer'),
        'question_text'=>$request->post('question_data'),
        'country_code'=>$request->post('country_code'),
        'standard_id'=>$request->post('standard_id'),
        'subcategory_id'=>$request->post('subcategory_id'),
        'input_symbols'=>$request->post('input_symbols'),
        'flag'=>'maths',
        'subject_id'=>$request->post('sub_id'),
        'solution'=>$request->post('solution'),
        'subcategory'=>$subcategory_name->name
       ]);
         return response()->json([
                'status'       =>true,
                'message'      => 'successfully Inserted'
            ], 200); 
    }

    public function question_details($id){
        $data=DB::table('questions')->where('id',$id)->first();
        return response()->json([
                'status'       =>true,
                'data'      =>$data
            ], 200); 
    }
}
