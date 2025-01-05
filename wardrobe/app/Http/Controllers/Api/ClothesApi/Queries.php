<?php

namespace App\Http\Controllers\Api\ClothesApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

// Models
use App\Models\ClothesModel;
use App\Models\ClothesUsedModel;
use App\Models\WashModel;

// Helpers
use App\Helpers\Generator;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/clothes/header/{category}/{order}",
     *     summary="Show all clothes (header)",
     *     tags={"Clothes"},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="clothes category",
     *         example="head",
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="ordering type",
     *         example="desc",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="clothes found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-321642910r4w"),
     *                         @OA\Property(property="clothes_name", type="string", example="Reebok Black Hatsss"),
     *                         @OA\Property(property="clothes_size", type="string", example="L"),
     *                         @OA\Property(property="clothes_gender", type="string", example="unisex"),
     *                         @OA\Property(property="clothes_color", type="string", example="black"),
     *                         @OA\Property(property="clothes_category", type="string", example="head"),
     *                         @OA\Property(property="clothes_type", type="string", example="hat"),
     *                         @OA\Property(property="clothes_qty", type="integer", example=1),
     *                         @OA\Property(property="is_faded", type="integer", example=0),
     *                         @OA\Property(property="has_washed", type="integer", example=1),
     *                         @OA\Property(property="has_ironed", type="integer", example=0),
     *                         @OA\Property(property="is_favorite", type="integer", example=0),
     *                         @OA\Property(property="is_scheduled", type="integer", example=1),
     *                     )
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="clothes not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function get_all_clothes_header(Request $request, $category, $order)
    {
        try{
            $user_id = $request->user()->id;

            $res = ClothesModel::select('id', 'clothes_name', 'clothes_size', 'clothes_gender', 'clothes_color', 'clothes_category', 'clothes_type', 'clothes_qty', 'is_faded', 'has_washed', 'has_ironed', 'is_favorite', 'is_scheduled');
            
            if($category != "all"){
                $res->where('clothes_category',$category);
            }
            
            $res = $res->where('created_by',$user_id)
                ->orderBy('is_favorite', 'desc')
                ->orderBy('clothes_name', $order)
                ->orderBy('created_at', $order)
                ->paginate(14);
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'clothes'),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "clothes"),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/clothes/trash",
     *     summary="Show all deleted clothes",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=200,
     *         description="clothes found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-321642910r4w"),
     *                         @OA\Property(property="clothes_name", type="string", example="Reebok Black Hatsss"),
     *                         @OA\Property(property="clothes_size", type="string", example="L"),
     *                         @OA\Property(property="clothes_gender", type="string", example="unisex"),
     *                         @OA\Property(property="clothes_color", type="string", example="black"),
     *                         @OA\Property(property="clothes_category", type="string", example="head"),
     *                         @OA\Property(property="clothes_type", type="string", example="hat"),
     *                         @OA\Property(property="clothes_qty", type="integer", example=1),
     *                         @OA\Property(property="deleted_at", type="string", format="date-time", example="2025-01-01 00:00:00")
     *                     )
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="clothes not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function get_deleted_clothes(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $res = ClothesModel::select('id', 'clothes_name', 'clothes_size', 'clothes_gender', 'clothes_color', 'clothes_category', 'clothes_type', 'clothes_qty', 'deleted_at')
                ->whereNotNull('deleted_at')
                ->where('created_by',$user_id)
                ->orderBy('deleted_at', 'desc')
                ->paginate(14);
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'clothes'),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "clothes"),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
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
     *         description="clothes found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-321642910r4w"),
     *                      @OA\Property(property="clothes_name", type="string", example="Reebok Black Hatsss"),
     *                      @OA\Property(property="clothes_category", type="string", example="head"),
     *                      @OA\Property(property="clothes_type", type="string", example="hat"),
     *                  )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="clothes not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function get_clothes_similiar_by(Request $request, $ctx, $val,$exc)
    {
        try{
            $user_id = $request->user()->id;

            $res = ClothesModel::select('id', 'clothes_name', 'clothes_category', 'clothes_type')
                ->where($ctx, 'like', "%$val%")                
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
                    'message' => Generator::getMessageTemplate("fetch", 'clothes'),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "clothes"),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/clothes/detail/{category}/{order}",
     *     summary="Show clothes detail",
     *     tags={"Clothes"},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="ordering type",
     *         example="desc",
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="clothes category",
     *         example="head",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="clothes found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-321642910r4w"),
     *                         @OA\Property(property="clothes_name", type="string", example="Reebok Black Hatsss"),
     *                         @OA\Property(property="clothes_desc", type="string", nullable=true, example=null),
     *                         @OA\Property(property="clothes_merk", type="string", example="Reebok"),
     *                         @OA\Property(property="clothes_size", type="string", example="-"),
     *                         @OA\Property(property="clothes_gender", type="string", example="unisex"),
     *                         @OA\Property(property="clothes_made_from", type="string", example="cloth"),
     *                         @OA\Property(property="clothes_color", type="string", example="black"),
     *                         @OA\Property(property="clothes_category", type="string", example="head"),
     *                         @OA\Property(property="clothes_type", type="string", example="hat"),
     *                         @OA\Property(property="clothes_price", type="integer", example=210000),
     *                         @OA\Property(property="clothes_buy_at", type="string", nullable=true, example=null),
     *                         @OA\Property(property="clothes_qty", type="integer", example=1),
     *                         @OA\Property(property="is_faded", type="integer", example=0),
     *                         @OA\Property(property="has_washed", type="integer", example=1),
     *                         @OA\Property(property="has_ironed", type="integer", example=0),
     *                         @OA\Property(property="is_favorite", type="integer", example=0),
     *                         @OA\Property(property="is_scheduled", type="integer", example=0),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-04-10 22:10:56"),
     *                         @OA\Property(property="created_by", type="string", example="2d98f524-de02-11ed-b5ea-0242ac120002"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", nullable=true, example=null),
     *                         @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example="2025-01-01 00:00:00")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="clothes not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
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
                ->paginate(14);
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'clothes'),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "clothes"),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/clothes/history/{clothes_id}/{order}",
     *     summary="Show clothes used history",
     *     tags={"Clothes"},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="ordering type",
     *         example="desc",
     *     ),
     *     @OA\Parameter(
     *         name="clothes_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="clothes id",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="clothes found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                          @OA\Property(property="clothes_name", type="string", example="Short Sleeves Oversized"),
     *                          @OA\Property(property="clothes_note", type="string", example="for sunny day"),
     *                          @OA\Property(property="used_context", type="string", example="Shopping"),
     *                          @OA\Property(property="created_at", type="string", example="2024-04-10 22:10:56"),
     *                      )
     *                  )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes used not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="clothes used not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
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
                ->paginate(14);
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'clothes used'),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "clothes used"),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     * @OA\GET(
     *     path="/api/v1/clothes/check_wash/{clothes_id}",
     *     summary="Show clothes wash status",
     *     tags={"Clothes"},
     *     @OA\Parameter(
     *         name="clothes_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="clothes id",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="clothes found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="This clothes is washed right now | This clothes is ready to use"),
     *             @OA\Property(property="data", type="bool", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="clothes not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
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
                        'message' => Generator::getMessageTemplate("custom", 'This clothes is washed right now'),
                        'data' => true
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("custom", 'This clothes is ready to use'),
                        'data' => false
                    ], Response::HTTP_OK);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "clothes"),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/clothes/detail/{clothes_id}",
     *     summary="Show clothes detail by clothes id",
     *     tags={"Clothes"},
     *     @OA\Parameter(
     *         name="clothes_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="clothes id",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clothes found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="This clothes is washed right now"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="detail", type="object",
     *                     @OA\Property(property="id", type="string", example="10bacb64-e819-11ed-a05b-0242ac120003"),
     *                     @OA\Property(property="clothes_name", type="string", example="Short Sleeves Oversized"),
     *                     @OA\Property(property="clothes_desc", type="string", example="Lorem ipsum odor amet, consectetuer adipiscing elit."),
     *                     @OA\Property(property="clothes_merk", type="string", example="Berskha"),
     *                     @OA\Property(property="clothes_size", type="string", example="L"),
     *                     @OA\Property(property="clothes_gender", type="string", example="male"),
     *                     @OA\Property(property="clothes_made_from", type="string", example="cloth"),
     *                     @OA\Property(property="clothes_color", type="string", example="black, white"),
     *                     @OA\Property(property="clothes_category", type="string", example="upper_body"),
     *                     @OA\Property(property="clothes_type", type="string", example="shirt"),
     *                     @OA\Property(property="clothes_price", type="integer", example=600000),
     *                     @OA\Property(property="clothes_buy_at", type="string", example=null),
     *                     @OA\Property(property="clothes_qty", type="integer", example=1),
     *                     @OA\Property(property="is_faded", type="integer", example=0),
     *                     @OA\Property(property="has_washed", type="integer", example=1),
     *                     @OA\Property(property="has_ironed", type="integer", example=1),
     *                     @OA\Property(property="is_favorite", type="integer", example=1),
     *                     @OA\Property(property="is_scheduled", type="integer", example=0),
     *                     @OA\Property(property="created_at", type="string", example="2024-04-10 22:10:56"),
     *                     @OA\Property(property="created_by", type="string", example="2d98f524-de02-11ed-b5ea-0242ac120002"),
     *                     @OA\Property(property="updated_at", type="string", example="2d98f524-de02-11ed-b5ea-0242ac120002"),
     *                     @OA\Property(property="deleted_at", type="string", example="2d98f524-de02-11ed-b5ea-0242ac120002")
     *                 ),
     *                 @OA\Property(property="used_history", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="1d7a826d-4898-c7a4-3b67-e3a01819a55c"),
     *                         @OA\Property(property="clothes_note", type="string", nullable=true, example=null),
     *                         @OA\Property(property="used_context", type="string", example="Shopping"),
     *                         @OA\Property(property="created_at", type="string", example="2024-05-14 02:32:07")
     *                     )
     *                 ),
     *                 @OA\Property(property="wash_history", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="wash_note", type="string", nullable=true, example=null),
     *                         @OA\Property(property="wash_type", type="string", example="Laundry"),
     *                         @OA\Property(property="wash_checkpoint", type="array",
     *                             @OA\Items(type="object",
     *                                 @OA\Property(property="id", type="string", example="1"),
     *                                 @OA\Property(property="checkpoint_name", type="string", example="Rendam"),
     *                                 @OA\Property(property="is_finished", type="boolean", example=false)
     *                             )
     *                         ),
     *                         @OA\Property(property="created_at", type="string", example="2024-05-17 04:09:40"),
     *                         @OA\Property(property="finished_at", type="string", nullable=true, example=null)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="clothes not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function get_clothes_detail_by_id(Request $request, $clothes_id)
    {
        try{
            $user_id = $request->user()->id;

            $res_clothes = ClothesModel::select('*')
                ->where('id',$clothes_id)
                ->where('created_by',$user_id)
                ->first();

            if($res_clothes){
                $res_used = ClothesUsedModel::select('id','clothes_note','used_context','created_at')
                    ->where('clothes_id',$clothes_id)
                    ->where('created_by',$user_id)
                    ->get();

                $res_wash = WashModel::select('wash_note','wash_type','wash_checkpoint','created_at','finished_at')
                    ->where('clothes_id',$clothes_id)
                    ->where('created_by',$user_id)
                    ->get();

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("custom", 'This clothes is washed right now'),
                    'data' => [
                        'detail' => $res_clothes,
                        'used_history' => count($res_used) > 0 ? $res_used : null,
                        'wash_history' => count($res_wash) > 0 ? $res_wash : null
                    ]
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "clothes"),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     * @OA\GET(
     *     path="/api/v1/clothes/wash_checkpoint/{clothes_id}",
     *     summary="Show clothes wash checkpoint",
     *     tags={"Clothes"},
     *     @OA\Parameter(
     *         name="clothes_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="clothes id",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="clothes found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="wash_note", type="string", example="Finish at 18:00"),
     *                      @OA\Property(property="wash_type", type="string", example="Laundry"),
     *                      @OA\Property(property="wash_checkpoint", type="array",
     *                          @OA\Items(type="object",
     *                              @OA\Property(property="id", type="string", example="1"),
     *                              @OA\Property(property="checkpoint_name", type="string", example="Rendam"),
     *                              @OA\Property(property="is_finished", type="boolean", example=false),
     *                          )
     *                      )
     *                  )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="wash checkpoint not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="wash checkpoint not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function get_wash_checkpoint_by_clothes_id(Request $request, $clothes_id)
    {
        try{
            $user_id = $request->user()->id;

            $res = WashModel::select('wash_note','wash_type','wash_checkpoint')
                ->where('clothes_id',$clothes_id)
                ->whereNull('finished_at')
                ->first();
                
            if ($res) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", "wash checkpoint"),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "wash checkpoint"),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
