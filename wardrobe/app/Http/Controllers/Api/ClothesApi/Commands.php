<?php

namespace App\Http\Controllers\Api\ClothesApi;
use App\Http\Controllers\Controller;

use App\Models\ClothesModel;
use App\Models\ClothesUsedModel;
use App\Models\WashModel;

use App\Helpers\Generator;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Commands extends Controller
{
    /**
     * @OA\DELETE(
     *     path="/api/v1/clothes/destroy/{id}",
     *     summary="Permentally delete clothes by id",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=200,
     *         description="clothes permentally deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes failed to permentally deleted"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function hard_delete_clothes_by_id(Request $request, $id)
    {
        try {
            $user_id = $request->user()->id;

            $rows = ClothesModel::destroy($id);

            if($rows > 0){
                return response()->json([
                    'status' => 'success',
                    'message' => 'clothes permentally deleted',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'clothes failed to permentally deleted',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. Please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/clothes/delete/{id}",
     *     summary="Delete clothes by id",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=200,
     *         description="clothes deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes failed to deleted"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function soft_delete_clothes_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            $rows = ClothesModel::where('id', $id)
                ->where('created_by', $user_id)
                ->update([
                    'deleted_at' => date('Y-m-d H:i:s'),
            ]);

            if($rows > 0){                
                return response()->json([
                    'status' => 'success',
                    'message' => 'clothes deleted',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'clothes failed to deleted',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. Please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/clothes/history",
     *     summary="Add clothes history",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=200,
     *         description="clothes deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes failed to deleted"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function post_history_clothes(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $res = ClothesUsedModel::create([
                'id' => Generator::get_uuid(),
                'clothes_id' => $request->clothes_id,
                'clothes_note' => $request->clothes_note,
                'used_context' => $request->used_context,
                'created_at' => date("Y-m-d H:i:s"),
                'created_by' => $user_id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'clothes create',
                'data' => $res
            ], Response::HTTP_OK);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. Please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/clothes/update_checkpoint/{id}",
     *     summary="Update clothes by id",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=200,
     *         description="clothes deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes failed to deleted"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function update_wash_by_clothes_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            $res = WashModel::where('clothes_id',$id)
            ->where('created_by',$user_id)
            ->whereNull('finished_at')
            ->update([
                'wash_checkpoint' => $request->wash_checkpoint,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'clothes update',
            ], Response::HTTP_OK);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. Please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     * @OA\DELETE(
     *     path="/api/v1/clothes/destroy_wash/{id}",
     *     summary="Permentally delete clothes by id",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=200,
     *         description="clothes permentally deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes failed to permentally deleted"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function hard_delete_wash_by_id(Request $request, $id)
    {
        $user_id = $request->user()->id;
        $rows = WashModel::destroy($id);

        if($rows > 0){
            return response()->json([
                'status' => 'success',
                'message' => 'clothes wash permentally deleted',
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'clothes wash failed to permentally deleted',
                'data' => null
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
