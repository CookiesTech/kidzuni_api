<?php

namespace App\Http\Controllers;

use  App\Models\Teachers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DB;
use Validator;

class TeacherController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth', [
            'except' => [
                'profile',

            ],
        ]);
    }

    public function profile()
    {

        $user = array('name' => Auth::user()->name, 'email' => Auth::user()->email, 'role' => 'SA');
        return response()->json(['status' => true, 'user' => $user], 200);
    }
    public function add(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'firstname'           => 'required',
            'username'           => 'required|unique:teachers',
            'email'           => 'required|unique:teachers'
        ]);

        if ($validator->fails()) {
            return $this->formatErrorResponse($validator);
        }
        DB::beginTransaction();
        try {
            $obj = new Teachers;
            $obj->first_name = $request->input('firstname');
            $obj->last_name = $request->input('lastname');
            $obj->username = $request->input('username');
            $obj->email = $request->input('email');
            $obj->role = 6;
            $obj->phone = $request->input('phone');
            $obj->password = $request->input('password');
            $obj->alternative_mobile = $request->input('alternative_mobile');
            $obj->qualification = $request->input('qualification');
            $obj->address = $request->input('address');
            $obj->profile_image = $request->input('profile_image');
            $obj->experience = $request->input('experience');
            $obj->previous_institution_name = $request->input('previous_institution_name');
            $obj->save();

            if (count($request->input('img')) > 0) {
                foreach ($request->input('img') as $key => $value) {

                    DB::table('teachers_images')->insert([
                        'teacher_master_id' => $obj->id, 'document_name' => $value['document_name'], 'image' => $value['image']
                    ]);
                }
            }

            if (count($request->input('std_id')) > 0) {
                foreach ($request->input('std_id') as $value) {
                    DB::table('teacher_sub_mapping')->insert([
                        'teacher_id' => $obj->id,
                        'standard_id' => $value['std_id'],
                        'subject_id' => $request->input('subject_id')
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
           
            return response()->json(['status' => false, 'message' => 'Something Went Wrong'], 200);
        }


        return response()->json(['status' => true, 'message' => 'Teachers Added Successfully'], 200);
    }

    public function getActive()
    {
        try {
            $user = Teachers::where('status', 1)->get();

            return response()->json(['status' => true, 'data' => $user], 200);
        } catch (\Exception $e) {

            return response()->json(['status' => false, 'data' => []], 404);
        }
    }

    public function getAll()
    {
        try {
            $user = Teachers::all();

            return response()->json(['status' => true, 'data' => $user], 200);
        } catch (\Exception $e) {

            return response()->json(['status' => false, 'data' => []], 404);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'           => 'required',
            'id'           => 'required'
        ]);

        if ($validator->fails()) {
            return $this->formatErrorResponse($validator);
        }


        $user = Teachers::where('id', $request->input('id'))->update([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'alternative_mobile' => $request->input('alternative_mobile'),
            'address' => $request->input('address'),
            'experience' => $request->input('experience'),
            'previous_institution_name' => $request->input('previous_institution_name'),
            'status' => 1
        ]);

        if (count($request->input('img')) > 0) {
            DB::table('teachers_images')->where('teacher_master_id', $request->input('id'))->delete();
            foreach ($request->input('img') as $key => $value) {

                DB::table('teachers_images')->insert([
                    'teacher_master_id' => $request->input('id'), 'document_name' => $value['document_name'], 'image' => $value['image']
                ]);
            }
        }


        return response()->json(['status' => true, 'message' => 'Teachers Updated Successfully'], 200);
    }

    public function delete_teacher($id)
    {

        Teachers::where('id', $id)->update(['status' => 0]);
        return response()->json(['status' => true, 'message' => 'Teachers Deleted Successfully'], 200);
    }

    public function get_teacherProfile($id)
    {
        $data['teacher'] = Teachers::where('id', $id)->first();
        $data['images'] = DB::table('teachers_images')->where('teacher_master_id', $id)->select('id', 'document_name', 'image')->get();
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function teacher_dashboard(Request $request)
    {
        $id=$request['user_id'];
        $data['rejected']=DB::table('questions')->where('teacher_id',$id)->where('approved_status','rejected')->count();
        $data['accepted']=DB::table('questions')->where('teacher_id',$id)->where('approved_status','approved')->count();
        $data['total_question']=DB::table('questions')->where('teacher_id',$id)->count();
        $data['latest_question']=DB::table('questions')->where('teacher_id',$id)->limit(10)->get();
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function teacher_questions_list(Request $request)
    {
        $id=$request['user_id'];
        $data['question']=DB::table('questions')->where('teacher_id',$id)->get();
        return response()->json(['status' => true, 'data' => $data]);
    }
   
    public function teacher_upload_question(Request $request)
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

                        if($standard_id){
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
                            'teacher_id'=>$request['user_id'],
                            'approved_status'=>'pending'
                                                        
                        ]);
                             $totalQuestionsAdded++;
                        }
                        #
                        else{
                            return response()->json([
                            'status'       => false,
                            'message'      => 'Standard Name Not found',
                        ], 200);
                        }
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
    public function teacher_insert_question(Request $request){
       
        $subcategory_name=DB::table('subcategory')->where('id', $request->post('subcategory_id'))->first('name');
        if($subcategory_name){
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
            'subcategory'=>$subcategory_name->name,
            'teacher_id'=>$request['user_id'],
            'approved_status'=>'pending'
        ]);
         return response()->json([
                'status'       =>true,
                'message'      => 'successfully Inserted'
            ], 200); 
        }else{
             return response()->json([
                'status'       =>true,
                'message'      => 'Subcategory_not Found'
            ], 200); 
        }
       
    }

    public function question_details($id){
        $data=DB::table('questions')->where('id',$id)->first();
        return response()->json([
                'status'       =>true,
                'data'      =>$data
            ], 200); 
    }





}
