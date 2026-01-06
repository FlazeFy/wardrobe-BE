<?php

namespace App\Http\Controllers\Api\DictionaryApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

// Models
use App\Models\DictionaryModel;
use App\Models\ClothesModel;
// Helpers
use App\Helpers\Generator;

class Queries extends Controller
{
    private $module;
    public function __construct()
    {
        $this->module = "dictionary";
    }

    /**
     * @OA\GET(
     *     path="/api/v1/dct/{type}",
     *     summary="Get Dictionary By Type",
     *     description="This request is used to get dictionary by its `dictionary_type`, that can be clothes_category, clothes_gender, clothes_made_from, clothes_size, clothes_type, day_name, track_source, used_context, wash_type, weather_hit_from. This request interacts with the MySQL database.",
     *     tags={"Dictionary"},
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="dictionary type",
     *         example="wash_type",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="dictionary fetched successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="dictionary_name", type="string", example="Laundry"),
     *                      @OA\Property(property="dictionary_type", type="string", example="wash_type"),
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
     *         description="dictionary not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="dictionary not found")
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
    public function get_dct_by_type(Request $request, $type)
    {
        try{
            // Get dictionary by type
            $res = DictionaryModel::getDictionaryByType($type);
            if (count($res) > 0) {
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
     *     path="/api/v1/dct/clothes/category_type",
     *     summary="Get Clothes Category And Type",
     *     description="This request is used to get list of clothes's category and type from existed clothes. This request interacts with the MySQL database and has protected route.",
     *     tags={"Dictionary"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="clothes category fetched successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes category type fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="clothes_category", type="string", example="head"),
     *                      @OA\Property(property="clothes_type", type="string", example="hat"),
     *                      @OA\Property(property="total", type="string", example=4),
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
     *         description="clothes category type not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="clothes category type not found")
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
    public function get_category_type_clothes(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            // Get clothes category and type
            $res = ClothesModel::getCategoryAndType($user_id);
            if ($res) {
                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", "clothes category type"),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "clothes category type"),
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
