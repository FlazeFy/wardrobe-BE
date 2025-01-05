<?php

namespace App\Http\Controllers\Api\StatsApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use DateTime;

// Models
use App\Models\ClothesModel;
use App\Models\ClothesUsedModel;
use App\Models\WashModel;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Validation;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/stats/clothes/summary",
     *     summary="Get stats summary",
     *     description="This request is used to get summary stats. This request is using MySql database, have a protected routes",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="total_clothes", type="integer", example=2),
     *                  @OA\Property(property="max_price", type="integer", example=600000),
     *                  @OA\Property(property="avg_price", type="integer", example=200000),
     *                  @OA\Property(property="sum_clothes_qty", type="integer", example=3)
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
     *         description="stats failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="stats not found")
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
    public function get_stats_summary(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $res = ClothesModel::selectRaw('
                COUNT(1) as total_clothes, MAX(clothes_price) as max_price, CAST(AVG(clothes_price) as UNSIGNED) as avg_price, CAST(SUM(clothes_qty) as UNSIGNED) as sum_clothes_qty')
                ->where('created_by',$user_id)
                ->first();
            
            if ($res) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
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
     *     path="/api/v1/stats/clothes/yearly/{ctx}",
     *     summary="Get yearly stats activity",
     *     description="This request is used to get yearly stats activity. This request is using MySql database, have a protected routes",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="context", type="string", example="2024-01-11"),
     *                      @OA\Property(property="total", type="integer", example=2),
     *                      @OA\Property(property="day", type="string", example="Sat")
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
     *         description="stats failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="stats not found")
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
    public function get_stats_yearly_context(Request $request, $ctx)
    {
        try{
            $request->merge(['context' => $ctx]);
            $validator = Validation::getValidateStats($request, 'yearly_context');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $user_id = $request->user()->id;
                $date_now = new DateTime();            
                $list_date = [];
                
                for ($i=1; $i <= 365; $i++) { 
                    $list_date[] = $date_now->format('Y-m-d');
                    $date_now->modify('-1 day');
                }

                if($ctx == "clothes_buy_at" || $ctx == "clothes_created_at"){
                    $target = $ctx == "clothes_created_at" ? "created_at" : "clothes_buy_at";
                    $res = ClothesModel::selectRaw("COUNT(1) as total, DATE($target) as context")
                        ->whereRaw("DATE($target) >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)")
                        ->groupByRaw("DATE($target)")
                        ->get();
                } else if($ctx == "wash_created_at"){
                    $res = WashModel::selectRaw("COUNT(1) as total, DATE(created_at) as context")
                        ->whereRaw("DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)")
                        ->groupByRaw("DATE(created_at)")
                        ->get();
                } else if($ctx == "clothes_used"){
                    $res = ClothesUsedModel::selectRaw("COUNT(1) as total, DATE(created_at) as context")
                        ->whereRaw("DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)")
                        ->groupByRaw("DATE(created_at)")
                        ->get();
                }
                
                if ($res) {
                    $final_res = [];
                    $total_all = 0;
                    foreach ($list_date as $date) {
                        $found = false;
                        $day = (new DateTime($date))->format('D');

                        foreach ($res as $dt) {
                            if($dt->context == $date){
                                $found = true;
                                $final_res[] = [
                                    'context' => $date,
                                    'total' => $dt->total,
                                    'day' => $day,
                                ];
                                $total_all = $total_all + $dt->total;
                                break;
                            }
                        }

                        if(!$found){
                            $final_res[] = [
                                'context' => $date,
                                'total' => 0,
                                'day' => $day
                            ];
                        }
                    }

                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("fetch", 'stats'),
                        'data' => $final_res,
                        'total_all' => $total_all
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'stats'),
                        'data' => $list_date
                    ], Response::HTTP_NOT_FOUND);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
