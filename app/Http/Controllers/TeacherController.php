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
            'username'           => 'required|unique:teachers'
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
}
