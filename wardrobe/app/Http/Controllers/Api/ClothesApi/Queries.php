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
use App\Models\OutfitUsedModel;
use App\Models\OutfitRelModel;
// Helpers
use App\Helpers\Generator;

class Queries extends Controller
{
    private $module;

    public function __construct()
    {
        $this->module = "clothes";
    }

    /**
     * @OA\GET(
     *     path="/api/v1/clothes/header/{category}/{order}",
     *     summary="Get All Clothes (Header)",
     *     description="This request is used to get all clothes (header information) by given `category` and `order`. This request interacts with the MySQL database, has a protected routes, and a pagination.",
     *     tags={"Clothes"},
     *     security={{"bearerAuth":{}}},
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
    public function getAllClothesHeader(Request $request, $category, $order){
        try{
            $user_id = $request->user()->id;
            $page = request()->query('page');  

            // Get all clothes header
            $res = ClothesModel::getAllClothesHeader($page, $category, $order, false, $user_id);
            if ($res && count($res) > 0) {
                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", $this->module),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", $this->module),
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
     *     summary="Get Deleted Clothes",
     *     description="This request is used to get all deleted clothes. This request interacts with the MySQL database, has a protected routes, and a pagination.",
     *     tags={"Clothes"},
     *     security={{"bearerAuth":{}}},
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
    public function getDeletedClothes(Request $request){
        try{
            $user_id = $request->user()->id;

            // Get deleted clothes
            $res = ClothesModel::getDeletedClothes($user_id);
            if ($res && count($res) > 0) {
                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", $this->module),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", $this->module),
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
     *     summary="Get Similar Clothes By Context",
     *     description="This request is used to get all deleted clothes. This request interacts with the MySQL database, has a protected routes, and a pagination.",
     *     tags={"Clothes"},
     *     security={{"bearerAuth":{}}},
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
    public function getClothesSimiliarBy(Request $request, $ctx, $val, $exc){
        try{
            $user_id = $request->user()->id;

            // Get clothes similiar by context
            $res = ClothesModel::getClothesSimiliarBy($ctx, $val, $user_id, $exc);
            if ($res && count($res) > 0) {
                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", $this->module),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", $this->module),
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
     *     summary="Get Clothes Detail By Category",
     *     description="This request is used to get all clothes detail by given `category` and `order`. This request interacts with the MySQL database, has a protected routes, and a pagination.",
     *     tags={"Clothes"},
     *     security={{"bearerAuth":{}}},
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
    public function getAllClothesDetail(Request $request, $category, $order){
        try{
            $user_id = $request->user()->id;
            $page = request()->query('page');  

            // Get all clothes (header format)
            $res = ClothesModel::getAllClothesHeader($page, $category, $order, true, $user_id);
            if ($res && count($res) > 0) {
                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", $this->module),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", $this->module),
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
     *     summary="Get Clothes Used History",
     *     description="This request is used to get clothes used history by given `clothes_id` and `order`. This request interacts with the MySQL database, has a protected routes, and a pagination.",
     *     tags={"Clothes"},
     *     security={{"bearerAuth":{}}},
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
    public function getClothesUsedHistory(Request $request, $clothes_id, $order){
        try{
            $user_id = $request->user()->id;
            $page = request()->query('page');  

            // Get clothes used history (detail)
            $res = ClothesUsedModel::getClothesUsedHistoryDetail($clothes_id, $user_id, $order, $page);
            if ($res && count($res) > 0) {
                // Return success response
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
     *     path="/api/v1/clothes/detail/{clothes_id}",
     *     summary="Get Clothes Detail By ID",
     *     description="This request is used to get clothes detail by given `clothes_id`. This request interacts with the MySQL database, has a protected routes, and a pagination.",
     *     tags={"Clothes"},
     *     security={{"bearerAuth":{}}},
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
    public function getClothesDetailByID(Request $request, $clothes_id){
        try{
            $user_id = $request->user()->id;

            // Get clothes by ID
            $res_clothes = ClothesModel::getClothesById($clothes_id, $user_id);
            if($res_clothes){
                // Get other relation
                $res_used = ClothesUsedModel::getClothesUsedHistory($clothes_id,$user_id);
                $res_wash = WashModel::getWashHistory($clothes_id,$user_id);
                $last_used = ClothesUsedModel::getLastUsed($user_id);
                $res_schedule = ScheduleModel::getScheduleByClothes($clothes_id, $user_id);
                $res_outfit = OutfitRelModel::getClothesFoundInOutfit($clothes_id,$user_id);
                $total_used = count($res_used);

                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", $this->module),
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
                    'message' => Generator::getMessageTemplate("not_found", $this->module),
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
     *     summary="Get Outfit Used History By ID",
     *     description="This request is used to get an outfit used history by using given `ID`. This request interacts with the MySQL database, and has a protected routes.",
     *     tags={"Clothes"},
     *     security={{"bearerAuth":{}}},
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
    public function getHistoryOutfitByID(Request $request, $id){
        try { 
            $user_id = $request->user()->id;

            // Get outfit history
            $res = OutfitUsedModel::getOutfitHistory($id,$user_id);
            if($res && count($res) > 0) {
                // Return success response            
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
     *     path="/api/v1/clothes/last_history",
     *     summary="Get Last History",
     *     description="This request is used to get the last history (summary). This request interacts with the MySQL database, and has a protected routes.",
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
    public function getLastHistory(Request $request){
        try { 
            $user_id = $request->user()->id;

            // Get last clothes (created)
            $res_last_added = ClothesModel::getLast('created_at',$user_id);
            if($res_last_added){
                // Get last clothes (deleted)
                $res_last_deleted = ClothesModel::getLast('deleted_at',$user_id);
                
                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", $this->module),
                    'data' => [
                        'last_added_clothes' => $res_last_added->clothes_name,
                        'last_added_date' => $res_last_added->created_at,
                        'last_deleted_clothes' => $res_last_deleted ? $res_last_deleted->clothes_name : null,
                        'last_deleted_date' => $res_last_deleted ? $res_last_deleted->deleted_at : null,
                    ]
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", $this->module),
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