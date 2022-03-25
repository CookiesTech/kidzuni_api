<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Validator;

class MainCategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

/**
     * @OA\Post(
     * path="/add",
     * description="add",
     * operationId="add",
     * tags={" Main Category"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass info",
     *    @OA\JsonContent(
     *       required={"start_date","end_date","filter","line_id","section_id"},
     *       @OA\Property(property="start_date", type="string", format="start_date", example="2022-02-03 0:0:0"),
     *       @OA\Property(property="end_date", type="string", format="end_date", example="2022-02-05 0:0:0"),
     *       @OA\Property(property="filter", type="string", format="filter", example="custom_date"),
     *      @OA\Property(property="line_id", type="integer", format="line_id", example="3"),
     *      @OA\Property(property="section_id", type="integer", format="section_id", example="6"),
     *      
     *    ),
     * ),
     *     @OA\Parameter(
     *         description="",
     *         in="header",
     *         name="token",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *         @OA\Response(
     *    response=200,
     *    description="success",
     *    @OA\JsonContent(
     *       @OA\Property(property="data", type="string", example= "{
     *  data: {employees:[{}] }}")
     *        )
     *     ),
     * )
     */
    public function add(Request $request)
    {

        if (!empty($request->post('data')[0]['category_name'])) {

            foreach ($request->post('data') as $key => $value) {

                if (DB::table('maincategory')->where('name', $value['category_name'])->where('standard_id', $request->post('standard'))->count() == 0) {
                    DB::table('maincategory')->insert(['name' => $value['category_name'], 'standard_id' => $request->post('standard'),'country_code'=>$request->post('country_code')]);
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
            $data = DB::table('maincategory as m')->join('standards as s','s.id','=','m.standard_id')
            ->join('countries as c','c.id','=','s.country_code')->select('m.id', 'm.name','s.standard_name','c.code','c.image')->orderBy('m.id','desc')->get();

            return response()->json(['status' => true, 'data' => $data], 200);
        } catch (\Exception $e) {

            return response()->json(['status' => false, 'data' => []], 200);
        }
    }
    
    public function getMainCategoryByStandardId(Request $request){

         return response()->json(['status' => true, 'data' => DB::table('maincategory')->where('standard_id',$request->post('standard_id'))->select('id','name')->get()], 200);
       
    }


    public function delete($id)
    {
        DB::table('maincategory')->where('id', $id)->delete();
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
}
