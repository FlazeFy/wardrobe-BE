<?php

namespace App\Http\Controllers\Api\ClothesApi;
use App\Http\Controllers\Controller;

// Models
use App\Models\ClothesModel;
use App\Models\ClothesUsedModel;
use App\Models\WashModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/clothes/header/{category}/{order}",
     *     summary="Show all clothes (header)",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=200,
     *         description="clothes found"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes failed to fetch"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function get_all_clothes_header(Request $request, $category, $order)
    {
        try{
            $user_id = $request->user()->id;

            $res = ClothesModel::select('id', 'clothes_name', 'clothes_size', 'clothes_gender', 'clothes_color', 'clothes_category', 'clothes_type', 'clothes_qty', 'is_faded', 'has_washed', 'has_ironed', 'is_favorite', 'is_scheduled')
                ->where('clothes_category',$category)
                ->where('created_by',$user_id)
                ->orderBy('is_favorite', 'desc')
                ->orderBy('clothes_name', $order)
                ->orderBy('created_at', $order)
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'clothes fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'clothes failed to fetched',
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

    /**
     * @OA\GET(
     *     path="/api/v1/clothes/similiar/{ctx}/{val}/{exc}",
     *     summary="Show similiar clothes by context",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=200,
     *         description="clothes found"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes failed to fetch"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function get_clothes_similiar_by(Request $request, $ctx, $val,$exc)
    {
        try{
            $user_id = $request->user()->id;

            $res = ClothesModel::select('id', 'clothes_name', 'clothes_category', 'clothes_type')
                ->where($ctx,$val)
                ->where('created_by',$user_id)
                ->whereNot('id',$exc)
                ->orderBy('is_favorite', 'desc')
                ->orderBy('clothes_name', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(12)
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'clothes fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'clothes failed to fetched',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error'.$e->getMessage(),
                'message' => 'something wrong. Please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/clothes/detail/{category}/{order}",
     *     summary="Show clothes detail",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=200,
     *         description="clothes found"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes failed to fetch"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function get_all_clothes_detail(Request $request, $category, $order)
    {
        try{
            $user_id = $request->user()->id;

            $res = ClothesModel::select('*')
                ->where('clothes_category',$category)
                ->where('created_by',$user_id)
                ->orderBy('is_favorite', 'desc')
                ->orderBy('clothes_name', $order)
                ->orderBy('created_at', $order)
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'clothes fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'clothes failed to fetched',
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

    /**
     * @OA\GET(
     *     path="/api/v1/clothes/history/{clothes_id}/{order}",
     *     summary="Show clothes used history",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=200,
     *         description="clothes found"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes failed to fetch"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function get_clothes_used_history(Request $request, $clothes_id, $order)
    {
        try{
            $user_id = $request->user()->id;

            $res = ClothesUsedModel::select('clothes_name','clothes_note','used_context','clothes.created_at')
                ->join('clothes','clothes.id','=','clothes_used.clothes_id')
                ->where('clothes_id',$clothes_id)
                ->orderBy('clothes_used.created_at', $order)
                ->orderBy('clothes_name', $order)
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'clothes used fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'clothes used failed to fetched',
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

     /**
     * @OA\GET(
     *     path="/api/v1/clothes/check_wash/{clothes_id}",
     *     summary="Show clothes wash status",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=200,
     *         description="clothes found"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes failed to fetch"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function get_clothes_wash_status_by_clothes_id(Request $request, $clothes_id)
    {
        try{
            $user_id = $request->user()->id;

            $exist = ClothesModel::selectRaw('1')
                ->where('id',$clothes_id)
                ->first();

            if($exist){
                $res = ClothesUsedModel::selectRaw('1')
                    ->where('clothes_id',$clothes_id)
                    ->first();
                
                if ($res) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'This clothes is washed right now',
                        'data' => true
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'This clothes is ready to use',
                        'data' => false
                    ], Response::HTTP_OK);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'This clothes is not exist',
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

     /**
     * @OA\GET(
     *     path="/api/v1/clothes/wash_checkpoint/{id}",
     *     summary="Show clothes wash checkpoint",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=200,
     *         description="clothes found"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes failed to fetch"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function get_wash_checkpoint_by_clothes_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            $res = WashModel::select('wash_note','wash_type','wash_checkpoint')
                ->where('clothes_id',$id)
                ->whereNull('finished_at')
                ->first();
                
            if ($res) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'wash checkpoint fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'wash checkpoint failed to fetched',
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
