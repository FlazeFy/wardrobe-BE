<?php

namespace App\Http\Controllers\Api\OutfitApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

// Models
use App\Models\ClothesModel;
use App\Models\OutfitModel;
use App\Models\OutfitUsedModel;
use App\Models\OutfitRelModel;
// Helpers
use App\Helpers\Generator;

class Queries extends Controller
{
    private $module;

    public function __construct()
    {
        $this->module = "outfit";
    }

    /**
     * @OA\GET(
     *     path="/api/v1/clothes/outfit",
     *     summary="Get All Outfit",
     *     description="This request is used to get all outfit. This request interacts with the MySQL database, has a protected routes, and a pagination.",
     *     tags={"Outfit"},
     *     security={{"bearerAuth":{}}},
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
    public function getAllOutfit(Request $request){
        try{
            $user_id = $request->user()->id;
            $limit = $request->limit ?? 8;

            // Get all outfit
            $res = OutfitModel::getAllOutfit($limit, $user_id);
            if ($res->count() > 0) {                
                $data = $res->getCollection()->map(function ($dt) {
                    // Get clothes in an outfit
                    $clothes = OutfitRelModel::getClothesByOutfitID($dt->id, $user_id);
                    $dt->clothes = $clothes;
                    return $dt;
                });
            
                $res->setCollection($data);

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
     *     path="/api/v1/clothes/outfit/last",
     *     summary="Get Last Created Outfit",
     *     description="This request is used to get last created outfit and its clothes. This request interacts with the MySQL database, and has a protected routes.",
     *     tags={"Outfit"},
     *     security={{"bearerAuth":{}}},
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
    public function getLastOutfit(Request $request){
        try { 
            $user_id = $request->user()->id;

            // Get outfit by ID
            $res = OutfitModel::getOneOutfit('last',null,$user_id);
            if ($res) {                
                // Get clothes in an outfit
                $clothes = OutfitRelModel::getClothesByOutfitID($res->id, $user_id);
                $clothes = $clothes->map(function ($item) {
                    return collect($item)->only(['clothes_name', 'clothes_type', 'clothes_image']);
                });

                $res->clothes = $clothes;

                // Return success response
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
     *     summary="Get Outfit And Its Clothes By Outfit ID",
     *     description="This request is used to get an outfit and its clothes by using given outfit's `ID`. This request interacts with the MySQL database, and has a protected routes.",
     *     tags={"Outfit"},
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
    public function getOutfitByID(Request $request, $id){
        try { 
            $user_id = $request->user()->id;

            // Get outfit by ID
            $res = OutfitModel::getOneOutfit('direct',$id,$user_id);
            if ($res) {  
                // Get clothes in an outfit              
                $clothes = OutfitRelModel::getClothesByOutfitID($id, $user_id);
                $res->clothes = $clothes;

                // Return success response
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
     *     path="/api/v1/clothes/outfit/summary",
     *     summary="Get Outfit Summary",
     *     description="This request is used to get the outfit summary. This request interacts with the MySQL database, and has a protected routes.",
     *     tags={"Outfit"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="outfit summary fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="outfit summary fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_outfit", type="integer", example=10),
     *                 @OA\Property(property="last_used", type="object",
     *                      @OA\Property(property="used_at", type="string", example="2025-04-16 11:52:25"),
     *                      @OA\Property(property="outfit_name", type="string", example="Outfit A"),
     *                 ),
     *                 @OA\Property(property="next_suggestion", type="string", example="Long Sleeves"),
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
     *         description="outfit summary not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="outfit summary not found")
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
    public function getOutfitSummary(Request $request){
        try { 
            $user_id = $request->user()->id;

            // Get last clothes (created)
            $res_last_added = ClothesModel::getLast('created_at',$user_id);
            if($res_last_added){
                // Get total outfit
                $res_total_outfit = OutfitModel::countOutfit($user_id);
                // Get last used outfit
                $res_last_used = OutfitUsedModel::getLastUsed($user_id);
                
                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", "outfit"),
                    'data' => [
                        'total_outfit' => $res_total_outfit,
                        'last_used' => $res_last_used,
                        'next_suggestion' => null,
                    ]
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
}