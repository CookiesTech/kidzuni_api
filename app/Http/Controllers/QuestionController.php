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
        #check student only logged or not
        if($request['role']==5){
             $subcategory_id=$request->post('subcategory_id');
            try {
            $data = DB::table('questions')->where('subcategory_id',$subcategory_id)->orderBy('id','desc')->inRandomOrder()->get();
            $score=DB::table('scores')->where('subcategory_id',$subcategory_id)->sum('score');

            return response()->json(['status' => true, 'data' => $data,'score'=>$score], 200);
        } catch (\Exception $e) {

            return response()->json(['status' => false, 'data' => []], 200);
        }
        }
        else{
            return response()->json(['status' => false, 'message' =>'unAuthorized'], 200);
        }
    }

     public function getrecommendations(Request $request)
    
    {
        #check student only logged or not
        if($request['role']==5){
             $standard_id=$request->post('standard_id');
            try {
            $data = DB::table('questions')->where('standard_id',$standard_id)->inRandomOrder()->first();
           
            return response()->json(['status' => true, 'data' => $data], 200);
        } catch (\Exception $e) {

            return response()->json(['status' => false, 'data' => []], 200);
        }
        }
        else{
            return response()->json(['status' => false, 'message' =>'unAuthorized'], 200);
        }
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
                      $mark = $sheet->getCell('L' . $row)->getValue();
                      $wrong_answermark_deduction = $sheet->getCell('M' . $row)->getValue();

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
                        $subcategory_id=DB::table('subcategory')->where('name', $subcategory)->select('id')->first();
                        $standard=DB::table('standards')->where('name', $standard)->select('id')->first();
                        DB::table('questions')->insert([
                            'subject_id'=>$subject_id,
                            'standard_id'=>$standard->id,
                            'subcategory'=>$subcategory,
                            'subcategory_id'=>$subcategory_id->id,
                            'country_code'=>$country_code,
                            'question_image'=>$question_image,
                            'question_text'=>$question_text,
                            'option1'=>$option1,
                            'option2'=>$option2,
                            'option3'=>$option3,
                            'option4'=>$option4,
                            'answer'=>$answer,
                            'mark'=>$mark,
                            'wrong_answer_mark'=>$wrong_answermark_deduction                                 
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
}
