<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

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
            $data = DB::table('questions')->get();

            return response()->json(['status' => true, 'data' => $data], 200);
        } catch (\Exception $e) {

            return response()->json(['status' => false, 'data' => []], 404);
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
            $totalEmployeeAdded = 0;
            $failedToImport     = [];

            // if ($missingHeaders = array_diff(['first_name', 'last_name', 'email', 'mobile', 'role_id', 'profile_picture', 'department_id', 'location_id', 'designation', 'username'], $headers)) {
            //     return response()->json([
            //         'success' => false,
            //         'result'  => ['file' => sprintf('Please choose valid file. Following %s missing <b>%s</b>', count($missingHeaders) > 1 ? 'headers are' : 'header is', implode(',', $missingHeaders))],
            //     ]);
            // }
            if ($tota_row > 1) {
                 for ($row = 2; $row <= $tota_row; $row++) {
                      $standard = $sheet->getCell('A' . $row)->getValue();
                      $subcategory = $sheet->getCell('B' . $row)->getValue();
                      $question_text = $sheet->getCell('C' . $row)->getValue();
                      $question_image = $sheet->getCell('D' . $row)->getValue();
                      $option1 = $sheet->getCell('E' . $row)->getValue();
                      $option2 = $sheet->getCell('F' . $row)->getValue();
                      $option3 = $sheet->getCell('G' . $row)->getValue();
                      $option4 = $sheet->getCell('H' . $row)->getValue();
                      $answer = $sheet->getCell('I' . $row)->getValue();
                      $mark = $sheet->getCell('J' . $row)->getValue();
                      $wrong_answermark_deduction = $sheet->getCell('K' . $row)->getValue();

                    $errorMessage = [];
                    if (empty($errorMessage) &&    empty($standard)) {
                        $errorMessage[] = 'First name is required';
                    }
                    if (empty($errorMessage) &&    empty($subcategory)) {
                        $errorMessage[] = 'User name is required';
                    }
                   
                     if (empty($errorMessage) &&    empty(DB::table('subcategory')->where('name', $subcategory)->first())) {
                        $errorMessage[] = sprintf('%s location id is Not Available. Check with superadmin', $subcategory);
                    }

                    if (!empty($errorMessage)) {
                        $dataToAdd['errors'] = implode('<br>', $errorMessage);
                        $failedToImport[]    = $dataToAdd;
                    } else {

                        DB::table('questions')->insert([
                            'standard'=>$standard,
                            'subcategory'=>$subcategory,
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
                             $totalEmployeeAdded++;
                    }

                 }


            } else {
                return response()->json([
                    'status'       => 'false',
                    'message'      => 'Add minimum one row data',
                ], 200);
            }

          return response()->json([
                'status'               => 'success',
                'failed_to_import'      => $failedToImport,
                'successfully_imported' => $totalEmployeeAdded,
            ], 200);   
        } catch (Exception $e) {
            $error_code = $e->errorInfo[1];
            return back()->withErrors('There was a problem uploading the data!');
        }
    }
}
