<?php

namespace App\Http\Controllers\Api\WashApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

// Models
use App\Models\ClothesModel;
use App\Models\WashModel;
// Helpers
use App\Helpers\Generator;

class Queries extends Controller
{
    private $module;

    public function __construct()
    {
        $this->module = "wash";
    }

    /**
     * @OA\GET(
     *     path="/api/v1/clothes/check_wash/{clothes_id}",
     *     summary="Get Clothes Wash Status By ID",
     *     description="This request is used to get clothes wash status by given `clothes_id`. This request interacts with the MySQL database, has a protected routes, and a pagination.",
     *     tags={"Wash"},
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
    public function getClothesWashStatusByClothesID(Request $request, $clothes_id){
        try{
            $user_id = $request->user()->id;

            // Get clothes by ID
            $exist = ClothesModel::getClothesById($clothes_id, $user_id);
            if($exist){
                $res = WashModel::getActiveWash($clothes_id, $user_id);
                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("custom", $res ? 'This clothes is washed right now' : 'This clothes is ready to use'),
                    'data' => $res ? true : false
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
     *     path="/api/v1/clothes/wash_checkpoint/{clothes_id}",
     *     summary="Get Clothes Wash Checkpoint",
     *     description="This request is used to get clothes wash checkpoint by given `clothes_id`. This request interacts with the MySQL database, and has a protected routes.",
     *     tags={"Wash"},
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
    public function getWashCheckpointByClothesID(Request $request, $clothes_id){
        try{
            $user_id = $request->user()->id;

            // Get wash by clothes ID
            $res = WashModel::getActiveWash($clothes_id,$user_id);
            if ($res) {
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
     *     path="/api/v1/clothes/wash_history",
     *     summary="Get Clothes Wash History",
     *     description="This request is used to get all clothes wash history. This request interacts with the MySQL database, has a protected routes, and a pagination.",
     *     tags={"Wash"},
     *     security={{"bearerAuth":{}}},
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
    public function getAllWashHistory(Request $request){
        try { 
            $user_id = $request->user()->id;
            $perPage = request()->input('per_page', 14);
            $page = request()->input('page', 1);
            $is_detailed = request()->input('is_detailed', false);

            // Get wash history
            $res = $is_detailed ? 
                WashModel::getWashExport($user_id, false) : 
                WashModel::getWashExport($user_id)->map(function ($col) {
                    unset($col->wash_checkpoint, $col->wash_note, $col->clothes_merk);
                    return $col;
                });

            // Convert to pagination
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
                    'message' => Generator::getMessageTemplate("not_found", $this->module),
                ], Response::HTTP_NOT_FOUND);
            } else {
                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", $this->module),
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
     *     summary="Get Unfinished Wash Clothes",
     *     description="This request is used to get clothes who is still at wash. This request interacts with the MySQL database, and has a protected routes",
     *     tags={"Wash"},
     *     security={{"bearerAuth":{}}},
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
    public function getUnfinishedWash(Request $request){
        try { 
            $user_id = $request->user()->id;
            $page = request()->query('page',1);  

            // Get unfinished wash
            $res = WashModel::getUnfinishedWash($user_id,$page);
            if ($res->isEmpty()) {         
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "unfinished $this->module"),
                ], Response::HTTP_NOT_FOUND);
            } else {
                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", "unfinished $this->module"),
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
}