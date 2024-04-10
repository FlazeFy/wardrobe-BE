<?php

namespace App\Http\Controllers\Api\ClothesApi;
use App\Http\Controllers\Controller;

use App\Models\ClothesModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Commands extends Controller
{
    public function hard_del_clothes_by_id($id)
    {
        $user_id = $request->user()->id;
        $clothes = ClothesModel::select('clothes_name')->where('id',$id)->first();

        $rows = ClothesModel::destroy($id);

        if($rows > 0){
            return response()->json([
                'status' => 'success',
                'message' => 'clothes permentally deleted',
                'data' => $res
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'clothes failed to permentally deleted',
                'data' => null
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function soft_del_clothes_by_id($id)
    {
        try{
            $user_id = $request->user()->id;
            $clothes = ClothesModel::select('clothes_name')->where('id',$id)->first();

            $rows = ClothesModel::where('id', $id)
                ->where('created_by', $user_id)
                ->update([
                    'deleted_at' => date('Y-m-d H:i:s'),
            ]);

            if($rows > 0){                
                return response()->json([
                    'status' => 'success',
                    'message' => 'clothes deleted',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'clothes failed to deleted',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. Please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
