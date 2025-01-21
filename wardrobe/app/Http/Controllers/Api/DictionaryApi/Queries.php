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
    /**
     * @OA\GET(
     *     path="/api/v1/dct/{type}",
     *     summary="Show dictionary by type",
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
     *         description="dictionary found",
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
            $res = DictionaryModel::select('dictionary_name','dictionary_type');
            if(strpos($type, ',')){
                $dcts = explode(",", $type);
                foreach ($dcts as $dt) {
                    $res = $res->orwhere('dictionary_type',$dt); 
                }
            } else {
                $res = $res->where('dictionary_type',$type); 
            }

            $res = $res->orderby('dictionary_type', 'ASC')
                ->orderby('dictionary_name', 'ASC')
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'dictionary'),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'dictionary'),
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
     *     summary="Show existed clothes category and clothes type",
     *     tags={"Dictionary"},
     *     @OA\Response(
     *         response=200,
     *         description="clothes category type found",
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

            $res = ClothesModel::getCategoryAndType($user_id);
                
            if ($res) {
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
