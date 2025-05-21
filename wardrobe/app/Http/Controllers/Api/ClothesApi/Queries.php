<?php

namespace App\Http\Controllers\Api\ClothesApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

// Models
use App\Models\ClothesModel;
use App\Models\ScheduleModel;
use App\Models\ClothesUsedModel;
use App\Models\WashModel;
use App\Models\OutfitModel;
use App\Models\OutfitUsedModel;
use App\Models\OutfitRelModel;

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
     *                         @OA\Property(property="clothes_image", type="string", example="https://storage.googleapis.com/download/storage/v1/b/wardrobe-26571.firebasestorage.app/o/clothes.png"),
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
            $page = request()->query('page');  

            $res = ClothesModel::select('id', 'clothes_name', 'clothes_image', 'clothes_size', 'clothes_gender', 'clothes_color', 'clothes_category', 'clothes_type', 'clothes_qty', 'is_faded', 'has_washed', 'has_ironed', 'is_favorite', 'is_scheduled');
            
            if($category != "all"){
                $res->where('clothes_category',$category);
            }
            
            $res = $res->where('created_by',$user_id)
                ->whereNull('deleted_at')
                ->orderBy('is_favorite', 'desc')
                ->orderBy('clothes_name', $order)
                ->orderBy('created_at', $order);

            if($page != "all"){
                $res = $res->paginate(14);
            } else {
                $res = $res->get();
            }
            
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
     *                         @OA\Property(property="clothes_image", type="string", example="https://storage.googleapis.com/download/storage/v1/b/wardrobe-26571.firebasestorage.app/o/clothes.png"),
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
            $res = ClothesModel::getDeletedClothes($user_id);
            
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

            $res = ClothesModel::select('id', 'clothes_name', 'clothes_image', 'clothes_category', 'clothes_type')
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
     *                         @OA\Property(property="clothes_image", type="string", example="https://storage.googleapis.com/download/storage/v1/b/wardrobe-26571.firebasestorage.app/o/clothes.png"),
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

            $res = ClothesModel::select('*');

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
     *                          @OA\Property(property="clothes_type", type="string", example="Hat"),
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
            $page = request()->query('page');  

            $res = ClothesUsedModel::select('clothes_used.id','clothes_name','clothes_type','clothes_note','used_context','clothes.created_at')
                ->join('clothes','clothes.id','=','clothes_used.clothes_id');

            if($clothes_id != "all"){
                $res = $res->where('clothes_id',$clothes_id);
            } 
            
            $res = $res->where('clothes_used.created_by',$user_id)
                ->orderBy('clothes_used.created_at', $order)
                ->orderBy('clothes_name', $order);

            if($clothes_id != "all" || $page != "all"){
                $res = $res->paginate(14);
            } else {
                $res = $res->get();
            }
            
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
     *                                 @OA\Property(property="is_finished", type="integer", example=0)
     *                             )
     *                         ),
     *                         @OA\Property(property="created_at", type="string", example="2024-05-17 04:09:40"),
     *                         @OA\Property(property="finished_at", type="string", nullable=true, example=null)
     *                     )
     *                 ),
     *                 @OA\Property(property="schedule", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="day", type="string", example="Sun"),
     *                         @OA\Property(property="is_remind", type="integer", example=1),
     *                         @OA\Property(property="schedule_note", type="string", example="Shopping"),
     *                         @OA\Property(property="created_at", type="string", example="2024-05-14 02:32:07")
     *                     )
     *                 ),
     *                 @OA\Property(property="outfit", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="outfit_name", type="string", example="Sun"),
     *                         @OA\Property(property="outfit_note", type="string", example="lorem"),
     *                         @OA\Property(property="is_favorite", type="integer", example=1),
     *                         @OA\Property(property="total_used", type="integer", example=0),
     *                         @OA\Property(property="last_used", type="string", example="2024-05-14 02:32:07"),
     *                         @OA\Property(property="created_at", type="string", example="2024-05-14 02:32:07")
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
    public function get_clothes_detail_by_id(Request $request, $clothes_id)
    {
        try{
            $user_id = $request->user()->id;

            $res_clothes = ClothesModel::select('*')
                ->where('id',$clothes_id)
                ->where('created_by',$user_id)
                ->first();

            if($res_clothes){
                $res_used = ClothesUsedModel::getClothesUsedHistory($clothes_id,$user_id);
                $res_wash = WashModel::getWashHistory($clothes_id,$user_id);
                $last_used = ClothesUsedModel::getLastUsed($user_id);
                $res_schedule = ScheduleModel::getScheduleByClothes($clothes_id, $user_id);
                $res_outfit = OutfitRelModel::getClothesFoundInOutfit($clothes_id,$user_id);
                $total_used = count($res_used);

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'clothes'),
                    'data' => [
                        'detail' => $res_clothes,
                        'used_history' =>  $total_used > 0 ? $res_used : null,
                        'total_used_history' => $total_used,
                        'last_used_history' => $last_used ? $last_used->created_at : null,
                        'wash_history' => count($res_wash) > 0 ? $res_wash : null,
                        'schedule' => count($res_schedule) > 0 ? $res_schedule : null,
                        'outfit' => count($res_outfit) > 0 ? $res_outfit: null,
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
     *                              @OA\Property(property="is_finished", type="integer", example=0),
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

            $res = WashModel::getActiveWash($clothes_id,$user_id);
                
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

    /**
     * @OA\GET(
     *     path="/api/v1/clothes/outfit",
     *     summary="Show all outfit",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=200,
     *         description="outfit found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="outfit fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="id", type="string", example="05d6fe1d-9041-5673-044b-4d2e7f6f0090"),
     *                      @OA\Property(property="outfit_name", type="string", example="Outfit Generated 17-Jan-2025 10:28"),
     *                      @OA\Property(property="outfit_note", type="string", example="Test 123"),
     *                      @OA\Property(property="is_favorite", type="integer", example=1),
     *                      @OA\Property(property="total_used", type="integer", example=2),
     *                      @OA\Property(property="clothes", type="array",
     *                          @OA\Items(type="object",
     *                              @OA\Property(property="id", type="string", example="05d6fe1d-9041-5673-044b-4d2e7f6f0090"),
     *                              @OA\Property(property="clothes_name", type="string", example="shirt A"),
     *                              @OA\Property(property="clothes_type", type="string", example="hat"),
     *                              @OA\Property(property="clothes_merk", type="string", example="nike"),
     *                              @OA\Property(property="clothes_image", type="string", example="https://storage.googleapis.com"),
     *                              @OA\Property(property="clothes_color", type="string", example="black"),
     *                              @OA\Property(property="has_washed", type="integer", example=0),
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
     *         description="outfit not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="outfit not found")
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
    public function get_all_outfit(Request $request)
    {
        try{
            $user_id = $request->user()->id;
            $limit = $request->limit ?? 8;

            $res = OutfitModel::getAllOutfit($limit,$user_id);

            if ($res->count() > 0) {                
                $data = $res->getCollection()->map(function ($dt) {
                    $clothes = OutfitRelModel::getClothesByOutfit($dt->id, "full");
                    $dt->clothes = $clothes;
                    return $dt;
                });
            
                $res->setCollection($data);

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", "outfit"),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "outfit"),
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
     *     path="/api/v1/clothes/outfit/last",
     *     summary="Show last outfit",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=200,
     *         description="outfit found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="outfit fetched"),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="string", example="05d6fe1d-9041-5673-044b-4d2e7f6f0090"),
     *                  @OA\Property(property="outfit_name", type="string", example="Outfit Generated 17-Jan-2025 10:28"),
     *                  @OA\Property(property="is_favorite", type="integer", example=1),
     *                  @OA\Property(property="total_used", type="integer", example=2),
     *                  @OA\Property(property="last_used", type="string", example="2024-04-10 22:10:56"),
     *                  @OA\Property(property="clothes", type="array",
     *                      @OA\Items(type="object",
     *                          @OA\Property(property="clothes_name", type="string", example="shirt A"),
     *                          @OA\Property(property="clothes_type", type="string", example="hat"),
     *                          @OA\Property(property="clothes_image", type="string", example="https://storage.googleapis.com"),
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
     *         description="outfit not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="outfit not found")
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
    public function get_last_outfit(Request $request)
    {
        try { 
            $user_id = $request->user()->id;

            $res = OutfitModel::getOneOutfit('last',null,$user_id);

            if ($res) {                
                $clothes = OutfitRelModel::getClothesByOutfit($res->id, "header");
                $res->clothes = $clothes;

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", "outfit"),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "outfit"),
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
     *     path="/api/v1/clothes/outfit/by/{id}",
     *     summary="Show outfit by id",
     *     tags={"Clothes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="outfit ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="outfit found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="outfit fetched"),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="string", example="05d6fe1d-9041-5673-044b-4d2e7f6f0090"),
     *                  @OA\Property(property="outfit_name", type="string", example="Outfit Generated 17-Jan-2025 10:28"),
     *                  @OA\Property(property="is_favorite", type="integer", example=1),
     *                  @OA\Property(property="total_used", type="integer", example=2),
     *                  @OA\Property(property="last_used", type="string", example="2024-04-10 22:10:56"),
     *                  @OA\Property(property="clothes", type="array",
     *                      @OA\Items(type="object",
     *                          @OA\Property(property="clothes_name", type="string", example="shirt A"),
     *                          @OA\Property(property="clothes_type", type="string", example="hat"),
     *                          @OA\Property(property="clothes_image", type="string", example="https://storage.googleapis.com"),
     *                          @OA\Property(property="clothes_desc", type="string", example="lorem ipsum"),
     *                          @OA\Property(property="is_favorite", type="boolean", example=1),
     *                          @OA\Property(property="has_washed", type="boolean", example=0),
     *                          @OA\Property(property="has_ironed", type="boolean", example=0),
     *                          @OA\Property(property="is_faded", type="boolean", example=1),
     *                          @OA\Property(property="clothes_merk", type="string", example="nike"),
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
     *         description="outfit not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="outfit not found")
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
    public function get_outfit_by_id(Request $request, $id)
    {
        try { 
            $user_id = $request->user()->id;

            $res = OutfitModel::getOneOutfit('direct',$id,$user_id);

            if ($res) {                
                $clothes = OutfitRelModel::select('clothes.id as id','clothes_name','clothes_type','clothes_image','is_favorite','has_washed','has_ironed','is_faded','clothes_merk')
                    ->join('clothes', 'clothes.id', '=', 'outfit_relation.clothes_id')
                    ->where('outfit_id', $res->id)
                    ->get();
                    
                $res->clothes = $clothes;

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", "outfit"),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "outfit"),
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
     *     path="/api/v1/clothes/outfit/history/{id}",
     *     summary="Show outfit history by id",
     *     tags={"Clothes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="outfit ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="history outfit found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="history outfit"),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="created_at", type="string", example="2025-01-17 16:50:18"),
     *                  @OA\Property(property="id", type="string", example="05d6fe1d-9041-5673-044b-4d2e7f6f0090")
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
     *         description="history outfit not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="history outfit not found")
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
    public function get_history_outfit_by_id(Request $request, $id){
        try { 
            $user_id = $request->user()->id;

            $res = OutfitUsedModel::getOutfitHistory($id,$user_id);

            if(count($res) > 0) {            
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", "history outfit"),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "history outfit"),
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
     *     path="/api/v1/clothes/schedule/{day}",
     *     summary="Show founded clothes in schedule by day",
     *     tags={"Clothes"},
     *     @OA\Parameter(
     *         name="day",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Day Name",
     *         example="Mon",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="schedule found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="schedule fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-321642910r4w"),
     *                      @OA\Property(property="clothes_name", type="string", example="Shirt ABC"),
     *                      @OA\Property(property="clothes_type", type="string", example="Shirt"),
     *                      @OA\Property(property="clothes_image", type="string", example="https://storage.googleapis.com/download/storage/v1/b/wardrobe-26571.firebasestorage.app/o/clothes.png"),
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
     *         description="schedule not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="schedule not found")
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
    public function get_schedule_by_day(Request $request, $day){
        try { 
            $user_id = $request->user()->id;

            $res = ScheduleModel::getScheduleByDay($day,$user_id);

            if(count($res) > 0) {            
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", "schedule"),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "schedule"),
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
     *     path="/api/v1/clothes/wash_history",
     *     summary="Show all clothes wash history",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=200,
     *         description="wash history found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="wash history fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="clothes_name", type="string", example="Shirt ABC"),
     *                      @OA\Property(property="wash_type", type="string", example="Laundry"),
     *                      @OA\Property(property="clothes_made_from", type="string", example="cloth"),
     *                      @OA\Property(property="clothes_color", type="string", example="black, white"),
     *                      @OA\Property(property="clothes_type", type="string", example="shirt"),
     *                      @OA\Property(property="wash_at", type="string", example="2024-05-17 04:09:40"),
     *                      @OA\Property(property="finished_at", type="string", example="2024-06-17 04:09:40"),
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
     *         description="wash history not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="wash history not found")
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
    public function get_all_wash_history(Request $request){
        try { 
            $user_id = $request->user()->id;
            $perPage = request()->input('per_page', 14);
            $page = request()->input('page', 1);
            $is_detailed = request()->input('is_detailed', false);

            $res = $is_detailed ? 
                WashModel::getWashExport($user_id, false) : 
                WashModel::getWashExport($user_id)->map(function ($col) {
                    unset($col->wash_checkpoint, $col->wash_note, $col->clothes_merk);
                    return $col;
                });

            $collection = collect($res);
            $collection = $collection->sortBy('wash_at')->values();
            $page = request()->input('page', 1);
            $paginator = new LengthAwarePaginator(
                $collection->forPage($page, $perPage)->values(),
                $collection->count(),
                $perPage,
                $page,
                ['path' => url()->current()]
            );
            $res = $paginator->appends(request()->except('page'));

            if ($res->isEmpty()) {         
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "wash"),
                ], Response::HTTP_NOT_FOUND);
            } else {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", "wash"),
                    'data' => $res
                ], Response::HTTP_OK);
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
     *     path="/api/v1/clothes/wash_unfinished",
     *     summary="Show unfinished wash clothes",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=200,
     *         description="unfinished wash found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="unfinished wash fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="clothes_name", type="string", example="Shirt ABC"),
     *                      @OA\Property(property="wash_type", type="string", example="Laundry"),
     *                      @OA\Property(property="wash_checkpoint", type="array",
     *                          @OA\Items(type="object",
     *                              @OA\Property(property="id", type="string", example="1"),
     *                              @OA\Property(property="checkpoint_name", type="string", example="Rendam"),
     *                              @OA\Property(property="is_finished", type="boolean", example=false),
     *                          )
     *                      ),
     *                      @OA\Property(property="clothes_type", type="string", example="shirt"),
     *                      @OA\Property(property="wash_at", type="string", example="2024-05-17 04:09:40")
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
     *         description="unfinished wash not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="unfinished wash not found")
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
    public function get_unfinished_wash(Request $request){
        try { 
            $user_id = $request->user()->id;
            $page = request()->query('page',1);  

            $res = WashModel::getUnfinishedWash($user_id,$page);

            if ($res->isEmpty()) {         
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "unfinished wash"),
                ], Response::HTTP_NOT_FOUND);
            } else {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", "unfinished wash"),
                    'data' => $res
                ], Response::HTTP_OK);
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
     *     path="/api/v1/clothes/schedule/tomorrow/{day}",
     *     summary="Get Tomorrow Schedule",
     *     description="This request fetches the schedule for tomorrow and two days later from the outfit and clothes schedule. It uses a MySQL database and is protected by authentication.",
     *     tags={"Clothes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="day",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Day of Today",
     *         example="Mon",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tomorrow schedule fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="tomorrow schedule fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="tomorrow", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="efbf49d9-78f4-436a-07ef-ca3aa661f9d7"),
     *                         @OA\Property(property="clothes_name", type="string", example="shirt A"),
     *                         @OA\Property(property="clothes_type", type="string", example="hat"),
     *                         @OA\Property(property="clothes_category", type="string", example="Upper Body"),
     *                         @OA\Property(property="clothes_image", type="string", example="https://image.jpg"),
     *                         @OA\Property(property="day", type="string", example="Mon"),
     *                     )
     *                 ),
     *                 @OA\Property(property="tomorrow_day", type="string", example="Mon"),
     *                 @OA\Property(property="two_days_later", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="two_days_later_day", type="string", example="Tue")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Protected route, requires authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tomorrow schedule not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="tomorrow schedule not found")
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
    public function get_schedule_tomorrow(Request $request, $day){
        try { 
            $user_id = $request->user()->id;

            $tomorrow = date('D', strtotime("next $day +1 day"));
            $two_days_later = date('D', strtotime("next $day +2 day"));

            $res_tomorrow = ScheduleModel::getScheduleByDay($tomorrow, $user_id);
            $res_2_days_later = ScheduleModel::getScheduleByDay($two_days_later, $user_id);
            $res = [
                'tomorrow' => count($res_tomorrow) > 0 ? $res_tomorrow : null,
                'tomorrow_day' => $tomorrow,
                'two_days_later' => count($res_2_days_later) > 0 ? $res_2_days_later : null,
                'two_days_later_day' => $two_days_later
            ];

            return response()->json([
                'status' => 'success',
                'message' => Generator::getMessageTemplate("fetch", "tomorrow schedule"),
                'data' => $res
            ], Response::HTTP_OK);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/clothes/last_history",
     *     summary="Get Last History",
     *     description="This request fetches the last history from clothes. It uses a MySQL database and is protected by authentication.",
     *     tags={"Clothes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="clothes last history fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes last history fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="last_added_clothes", type="string", example="Short Shoes"),
     *                 @OA\Property(property="last_added_date", type="string", example="2024-05-17 04:09:40"),
     *                 @OA\Property(property="last_deleted_clothes", type="string", example="Long Sleeves"),
     *                 @OA\Property(property="last_deleted_date", type="string", example="2024-05-17 04:09:40")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Protected route, requires authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="clothes last history not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="clothes last history not found")
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
    public function get_last_history(Request $request){
        try { 
            $user_id = $request->user()->id;

            $res_last_added = ClothesModel::getLast('created_at',$user_id);
            if($res_last_added){
                $res_last_deleted = ClothesModel::getLast('deleted_at',$user_id);
                $res = [
                    'last_added_clothes' => $res_last_added->clothes_name,
                    'last_added_date' => $res_last_added->created_at,
                    'last_deleted_clothes' => $res_last_deleted ? $res_last_deleted->clothes_name : null,
                    'last_deleted_date' => $res_last_deleted ? $res_last_deleted->deleted_at : null,
                ];
                
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", "clothes last history"),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "clothes last history"),
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
