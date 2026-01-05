<?php

namespace App\Http\Controllers\Api\StatsApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use DateTime;
use DateInterval;
use DatePeriod;

// Models
use App\Models\ClothesModel;
use App\Models\ClothesUsedModel;
use App\Models\WashModel;
use App\Models\UserModel;
use App\Models\ScheduleModel;
use App\Models\OutfitModel;
use App\Models\OutfitUsedModel;
use App\Models\FeedbackModel;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Validation;

class Queries extends Controller
{
    private $month;

    public function __construct()
    {
        $this->months = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec'
        ];
    }

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
            if ($request->hasHeader('Authorization')) {
                $user = Auth::guard('sanctum')->user(); 
                $user_id = $user ? $user->id : null;
            } else {
                $user_id = null;
            }
            
            $res = ClothesModel::getStatsSummary($user_id);
            
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
     *     path="/api/v1/stats/all",
     *     summary="Get stats summary",
     *     description="This request is used to get all summary. This request is using MySql database",
     *     tags={"Stats"},
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="total_clothes", type="integer", example=2),
     *                      @OA\Property(property="total_user", type="integer", example=2),
     *                      @OA\Property(property="total_schedule", type="integer", example=2),
     *                      @OA\Property(property="total_outfit_decision", type="integer", example=3)
     *                  )
     *             )
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
    public function get_all_stats()
    {
        try{
            $total_clothes = ClothesModel::whereNull('deleted_at')->count();
            $total_user = UserModel::count();
            $total_schedule = ScheduleModel::count();
            $total_outfit_decision = OutfitModel::where('is_auto',1)->count();
            
            return response()->json([
                'status' => 'success',
                'message' => Generator::getMessageTemplate("fetch", 'stats'),
                'data' => [
                    'total_clothes' => $total_clothes,
                    'total_user' => $total_user,
                    'total_schedule' => $total_schedule,
                    'total_outfit_decision' => $total_outfit_decision,
                ]
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
     *     path="/api/v1/stats/feedback/top",
     *     summary="Get Top Feedback",
     *     description="This request is used to get top 4 feedback by rate. This request is using MySql database",
     *     tags={"Stats"},
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="feedback_rate", type="integer", example=5),
     *                      @OA\Property(property="feedback_body", type="string", example="thats great!"),
     *                      @OA\Property(property="created_at", type="string", example="2025-01-06 03:04:54"),
     *                      @OA\Property(property="username", type="string", example="flazefy")
     *                  )
     *             ),
     *             @OA\Property(property="total", type="integer", example=2),
     *             @OA\Property(property="average", type="integer", example=2),
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
    public function get_top_feedback()
    {
        try{
            $res = FeedbackModel::getTopFeedback();

            if ($res) {
                $total = FeedbackModel::count();
                $average = FeedbackModel::avg('feedback_rate');
                $average = ceil($average * 100) / 100;

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $res,
                    'total' => $total,
                    'average' => $average
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
                $date_now = new DateTime();            
                $list_date = [];

                if ($request->hasHeader('Authorization')) {
                    $user = Auth::guard('sanctum')->user(); 
                    $user_id = $user ? $user->id : null;
                } else {
                    $user_id = null;
                }
                
                for ($i=1; $i <= 365; $i++) { 
                    $list_date[] = $date_now->format('Y-m-d');
                    $date_now->modify('-1 day');
                }

                if($ctx == "clothes_buy_at" || $ctx == "clothes_created_at"){
                    $target = $ctx == "clothes_created_at" ? "created_at" : "clothes_buy_at";
                    $res = ClothesModel::getYearlyClothesCreatedBuyed($user_id, $target);
                } else if($ctx == "wash_created_at"){
                    $res = WashModel::getYearlyWash($user_id);
                } else if($ctx == "clothes_used"){
                    $res = ClothesUsedModel::getYearlyClothesUsed($user_id);
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

     /**
     * @OA\GET(
     *     path="/api/v1/stats/clothes/monthly/created_buyed/{year}",
     *     summary="Get Clothes Monthly Created Buyed",
     *     description="This request is used to get yearly stats activity. This request is using MySql database, have a protected routes",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="year of created date and buy at",
     *         example="2024",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="context", type="string", example="Jan"),
     *                      @OA\Property(property="total_created", type="integer", example=2),
     *                      @OA\Property(property="total_buyed", type="integer", example=1)
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
    public function get_stats_clothes_monthly_created_buyed(Request $request, $year) {
        try {
            $date_now = new DateTime();
            $list_date = [];
            if ($request->hasHeader('Authorization')) {
                $user = Auth::guard('sanctum')->user(); 
                $user_id = $user ? $user->id : null;
            } else {
                $user_id = null;
            }
    
            $res_created = ClothesModel::getMonthlyClothesCreatedBuyed($user_id, $year, 'created_at');
            $res_buyed = ClothesModel::getMonthlyClothesCreatedBuyed($user_id, $year, 'clothes_buy_at');
    
            if ($res_created->isNotEmpty() || $res_buyed->isNotEmpty()) {
                $final_res = [];
    
                foreach ($this->months as $month_number => $month_name) {
                    $found_created = $res_created->firstWhere('context', $month_number);
                    $found_buyed = $res_buyed->firstWhere('context', $month_number);
    
                    $final_res[] = [
                        'context' => $month_name,  
                        'total_created' => $found_created ? $found_created->total : 0,
                        'total_buyed' => $found_buyed ? $found_buyed->total : 0,
                    ];
                }
    
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $final_res,
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/stats/clothes/monthly/used/{year}",
     *     summary="Get Clothes Monthly Used",
     *     description="This request is used to get total used clothes per month. This request is using MySql database, have a protected routes",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="year of created date and buy at",
     *         example="2024",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="context", type="string", example="Jan"),
     *                      @OA\Property(property="total", type="integer", example=2),
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
    public function get_stats_clothes_monthly_used(Request $request, $year) {
        try {
            $user_id = $request->user()->id;    
            $res = ClothesModel::getMonthlyClothesUsed($user_id, $year);
    
            if ($res->isNotEmpty()) {
                $final_res = [];
    
                foreach ($this->months as $month_number => $month_name) {
                    $found = $res->firstWhere('context', $month_number);
    
                    $final_res[] = [
                        'context' => $month_name,  
                        'total' => $found ? $found->total : 0,
                    ];
                }
    
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $final_res,
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    

    /**
     * @OA\GET(
     *     path="/api/v1/stats/calendar/{month}/{year}",
     *     summary="Get calendar history for used history, weekly schedule, wash history, buyed history, and add to wardrobe",
     *     description="This request is used to get calendar history for used history, weekly schedule, wash history, buyed history, and add to wardrobe. This request is using MySql database, have a protected routes",
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
     *                      @OA\Property(property="date", type="string", example="10 05 2024"),
     *                      @OA\Property(property="used_history", type="array",
     *                          @OA\Items(type="object",
     *                              @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-3216422910r4"),
     *                              @OA\Property(property="clothes_name", type="string", example="Reebok Black Hat"),
     *                              @OA\Property(property="clothes_category", type="string", example="head"),
     *                              @OA\Property(property="clothes_type", type="string", example="hat"),
     *                              @OA\Property(property="created_at", type="string", example="2024-05-10 22:10:56"),
     *                          )
     *                      ),
     *                      @OA\Property(property="weekly_schedule", type="array",
     *                          @OA\Items(type="object",
     *                              @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-3216422910r4"),
     *                              @OA\Property(property="clothes_name", type="string", example="Reebok Black Hat"),
     *                              @OA\Property(property="clothes_category", type="string", example="head"),
     *                              @OA\Property(property="clothes_type", type="string", example="hat"),
     *                              @OA\Property(property="day", type="string", example="Sun"),
     *                          )
     *                      ),
     *                      @OA\Property(property="wash_schedule", type="array",
     *                          @OA\Items(type="object",
     *                              @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-3216422910r4"),
     *                              @OA\Property(property="clothes_name", type="string", example="Reebok Black Hat"),
     *                              @OA\Property(property="clothes_category", type="string", example="head"),
     *                              @OA\Property(property="clothes_type", type="string", example="hat"),
     *                              @OA\Property(property="created_at", type="string", example="2024-05-10 22:10:56"),
     *                          )
     *                      ),
     *                      @OA\Property(property="buyed_history", type="array",
     *                          @OA\Items(type="object",
     *                              @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-3216422910r4"),
     *                              @OA\Property(property="clothes_name", type="string", example="Reebok Black Hat"),
     *                              @OA\Property(property="clothes_category", type="string", example="head"),
     *                              @OA\Property(property="clothes_type", type="string", example="hat"),
     *                              @OA\Property(property="created_at", type="string", example="2024-05-10 22:10:56"),
     *                          )
     *                      ),
     *                      @OA\Property(property="add_wardrobe", type="array", 
     *                          @OA\Items(type="object",
     *                              @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-3216422910r4"),
     *                              @OA\Property(property="clothes_name", type="string", example="Reebok Black Hat"),
     *                              @OA\Property(property="clothes_category", type="string", example="head"),
     *                              @OA\Property(property="clothes_type", type="string", example="hat"),
     *                              @OA\Property(property="created_at", type="string", example="2024-05-10 22:10:56"),
     *                          )
     *                      ),
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
    public function get_stats_calendar(Request $request, $month, $year){
        try{
            if ($month < 1 || $month > 12) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'month is not valid'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $user_id = $request->user()->id;
                $startDate = new DateTime("$year-$month-01");
                $endDate = clone $startDate;
                $endDate->modify('first day of next month');
                $interval = new DateInterval('P1D'); // End date is first day of next month
                $datePeriod = new DatePeriod($startDate, $interval, $endDate);       

                $res_used_history = ClothesUsedModel::getClothesUsedHistoryCalendar($user_id, $year, $month);
                $res_wash_schedule = WashModel::getWashCalendar($user_id, $year, $month);
                $res_weekly_schedule = ScheduleModel::getWeeklyScheduleCalendar($user_id);
                $res_buyed_history = ClothesModel::getClothesBuyedCalendar($user_id, $year, $month);
                $res_add_wardrobe = ClothesModel::getClothesCreatedCalendar($user_id, $year, $month);

                $final_res = [];
                $format_date = 'd M Y';

                foreach ($datePeriod as $date) {
                    $dateDt = clone $date;
                    $date = $date->format($format_date);

                    $curr_res_used_history = [];
                    foreach ($res_used_history as $dt) {
                        if(date($format_date,strtotime($dt->created_at)) == $date){
                            $curr_res_used_history[] = $dt;
                        }
                    }
                    $curr_res_weekly_schedule = [];
                    foreach ($res_weekly_schedule as $dt) {
                        if($dt->day == $dateDt->format('D')){
                            $curr_res_weekly_schedule[] = $dt;
                        }
                    }
                    $curr_res_wash_schedule = [];
                    foreach ($res_wash_schedule as $dt) {
                        if(date($format_date,strtotime($dt->created_at)) == $date){
                            $curr_res_wash_schedule[] = $dt;
                        }
                    }
                    $curr_res_buyed_history = [];
                    foreach ($res_buyed_history as $dt) {
                        if(date($format_date,strtotime($dt->created_at)) == $date){
                            $curr_res_buyed_history[] = $dt;
                        }
                    }
                    $curr_res_add_wardrobe = [];
                    foreach ($res_add_wardrobe as $dt) {
                        if(date($format_date,strtotime($dt->created_at)) == $date){
                            $curr_res_add_wardrobe[] = $dt;
                        }
                    }

                    $final_res[] = [
                        'date' => $date,
                        'used_history' => count($curr_res_used_history) > 0 ? $curr_res_used_history : null,
                        'weekly_schedule' => count($curr_res_weekly_schedule) > 0 ? $curr_res_weekly_schedule : null,
                        'wash_schedule' => count($curr_res_wash_schedule) > 0 ? $curr_res_wash_schedule : null,
                        'buyed_history' => count($curr_res_buyed_history) > 0 ? $curr_res_buyed_history : null,
                        'add_wardrobe' => count($curr_res_add_wardrobe) > 0 ? $curr_res_add_wardrobe : null,
                    ];
                }   

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $final_res,
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
     *     path="/api/v1/stats/calendar/detail/date/{date}",
     *     summary="Get calendar history for used history, weekly schedule, wash history, buyed history, and add to wardrobe for a specific date",
     *     description="This request is used to get calendar history for used history, weekly schedule, wash history, buyed history, and add to wardrobe for a specific date. This request is using MySql database, have a protected routes",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="used_history", type="object",
     *                     @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-3216422910r4"),
     *                     @OA\Property(property="clothes_name", type="string", example="Reebok Black Hat"),
     *                     @OA\Property(property="clothes_category", type="string", example="head"),
     *                     @OA\Property(property="clothes_type", type="string", example="hat"),
     *                     @OA\Property(property="created_at", type="string", example="2024-05-10 22:10:56"),
     *                 ),
     *                 @OA\Property(property="weekly_schedule", type="object",
     *                     @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-3216422910r4"),
     *                     @OA\Property(property="clothes_name", type="string", example="Reebok Black Hat"),
     *                     @OA\Property(property="clothes_category", type="string", example="head"),
     *                     @OA\Property(property="clothes_type", type="string", example="hat"),
     *                     @OA\Property(property="day", type="string", example="Sun"),
     *                 ),
     *                 @OA\Property(property="wash_schedule", type="object",
     *                     @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-3216422910r4"),
     *                     @OA\Property(property="clothes_name", type="string", example="Reebok Black Hat"),
     *                     @OA\Property(property="clothes_category", type="string", example="head"),
     *                     @OA\Property(property="clothes_type", type="string", example="hat"),
     *                     @OA\Property(property="created_at", type="string", example="2024-05-10 22:10:56"),
     *                 ),
     *                 @OA\Property(property="buyed_history", type="object",
     *                     @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-3216422910r4"),
     *                     @OA\Property(property="clothes_name", type="string", example="Reebok Black Hat"),
     *                     @OA\Property(property="clothes_category", type="string", example="head"),
     *                     @OA\Property(property="clothes_type", type="string", example="hat"),
     *                     @OA\Property(property="created_at", type="string", example="2024-05-10 22:10:56"),
     *                 ),
     *                 @OA\Property(property="add_wardrobe", type="object",
     *                     @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-3216422910r4"),
     *                     @OA\Property(property="clothes_name", type="string", example="Reebok Black Hat"),
     *                     @OA\Property(property="clothes_category", type="string", example="head"),
     *                     @OA\Property(property="clothes_type", type="string", example="hat"),
     *                     @OA\Property(property="created_at", type="string", example="2024-05-10 22:10:56"),
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
    public function get_stats_calendar_by_date(Request $request, $date){
        try{
            $date_check = DateTime::createFromFormat('Y-m-d', $date);

            if ($date_check && $date_check->format('Y-m-d') !== $date) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'date is not valid'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $user_id = $request->user()->id;
                $date = new DateTime($date);

                $res_used_history = ClothesUsedModel::getClothesUsedHistoryCalendar($user_id, null, null, $date);
                $res_wash_schedule = WashModel::getWashCalendar($user_id, null, null, $date);
                $res_weekly_schedule = ScheduleModel::getWeeklyScheduleCalendar($user_id);
                $res_buyed_history = ClothesModel::getClothesBuyedCalendar($user_id, null, null, $date);
                $res_add_wardrobe = ClothesModel::getClothesCreatedCalendar($user_id, null, null, $date);

                $final_res = [];
                $format_date = 'd M Y';
                $dateDt = clone $date;
                $date = $date->format($format_date);

                $curr_res_used_history = [];
                foreach ($res_used_history as $dt) {
                    $curr_res_used_history[] = $dt;
                }
                $curr_res_weekly_schedule = [];
                foreach ($res_weekly_schedule as $dt) {
                    if($dt->day == $dateDt->format('D')){
                        $curr_res_weekly_schedule[] = $dt;
                    }
                }
                $curr_res_wash_schedule = [];
                foreach ($res_wash_schedule as $dt) {
                    $curr_res_wash_schedule[] = $dt;
                }
                $curr_res_buyed_history = [];
                foreach ($res_buyed_history as $dt) {
                    $curr_res_buyed_history[] = $dt;
                }
                $curr_res_add_wardrobe = [];
                foreach ($res_add_wardrobe as $dt) {
                    $curr_res_add_wardrobe[] = $dt;
                }

                $final_res = [
                    'used_history' => count($curr_res_used_history) > 0 ? $curr_res_used_history : null,
                    'weekly_schedule' => count($curr_res_weekly_schedule) > 0 ? $curr_res_weekly_schedule : null,
                    'wash_schedule' => count($curr_res_wash_schedule) > 0 ? $curr_res_wash_schedule : null,
                    'buyed_history' => count($curr_res_buyed_history) > 0 ? $curr_res_buyed_history : null,
                    'add_wardrobe' => count($curr_res_add_wardrobe) > 0 ? $curr_res_add_wardrobe : null,
                ];

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $final_res,
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
     *     path="/api/v1/stats/outfit/monthly/by_outfit/{year}/{outfit_id}",
     *     summary="Get Outfit Monthly Total Used",
     *     description="This request is used to get yearly stats outfit used. This request is using MySql database, have a protected routes",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="year of used date",
     *         example="2024",
     *     ),
     *     @OA\Parameter(
     *         name="outfit_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="outfit id",
     *         example="05d6fe1d-9041-5673-044b-4d2e7f6f0090",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="context", type="string", example="Jan"),
     *                      @OA\Property(property="total", type="integer", example=2)
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
    public function get_stats_outfit_monthly_by_outfit_id(Request $request, $year, $outfit_id){
        try {
            if ($request->hasHeader('Authorization')) {
                $user = Auth::guard('sanctum')->user(); 
                $user_id = $user ? $user->id : null;
            } else {
                $user_id = null;
            }
            $date_now = new DateTime();
            $list_date = [];
    
            $res = OutfitUsedModel::getMonthlyUsedOutfitByOutfitID($year, $outfit_id, $user_id);
    
            if ($res->isNotEmpty()) {
                $final_res = [];
    
                foreach ($this->months as $month_number => $month_name) {
                    $found = $res->firstWhere('context', $month_number);
    
                    $final_res[] = [
                        'context' => $month_name,  
                        'total' => $found ? $found->total : 0,
                    ];
                }
    
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $final_res,
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
                    'data' => $list_date
                ], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/stats/outfit/most/used/{year}",
     *     summary="Get Yearly Most Used Outfit",
     *     description="This request is used to get yearly most used outfit. This request is using MySql database, have a protected routes",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="year of used outfit",
     *         example="2024",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="context", type="string", example="Outfit A"),
     *                      @OA\Property(property="total", type="integer", example=10)
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
    public function get_stats_outfit_yearly_most_used(Request $request,$year){
        try {
            if ($request->hasHeader('Authorization')) {
                $user = Auth::guard('sanctum')->user(); 
                $user_id = $user ? $user->id : null;
            } else {
                $user_id = null;
            }
            $limit = request()->query('limit');

            $res = OutfitUsedModel::getOutfitMostUsed($year, $user_id, $limit);
    
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $res,
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/stats/wash/summary",
     *     summary="Get Wash Summary",
     *     description="This request is used to get wash summary. This request is using MySql database, have a protected routes",
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
     *                      @OA\Property(property="avg_wash_per_week", type="integer", example=10),
     *                      @OA\Property(property="avg_wash_dur_per_clothes", type="integer", example=24),
     *                      @OA\Property(property="total_wash", type="integer", example=32),
     *                      @OA\Property(property="most_wash", type="string", example="Short Sleeves Oversized"),
     *                      @OA\Property(property="last_wash_clothes", type="string", example="Short Sleeves Oversized"),
     *                      @OA\Property(property="last_wash_date", type="string", example="2024-05-17 04:09:40"),
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
    public function get_stats_wash_summary(Request $request){
        try {
            $user_id = $request->user()->id;

            $res_last_wash = WashModel::getLastWash($user_id);
            if ($res_last_wash) {
                $res_summary = WashModel::getWashSummary($user_id);

                $res = [
                    'last_wash_clothes' => $res_last_wash->clothes_name,
                    'last_wash_date' => $res_last_wash->wash_at,
                    'total_wash' => $res_summary->total_wash,
                    'most_wash' => $res_summary->most_wash,
                    'avg_wash_dur_per_clothes' => round($res_summary->avg_wash_dur_per_clothes),
                    'avg_wash_per_week' => round($res_summary->avg_wash_per_week)
                ];

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $res,
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/stats/clothes/most/used/daily",
     *     summary="Get Most Used Daily Clothes By Type",
     *     description="This request is used to get most used daily clothes by type. This request is using MySql database, have a protected routes",
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
     *                      @OA\Property(property="day", type="string", example="Sun"),
     *                      @OA\Property(property="clothes", type="array",
     *                          @OA\Items(type="object",
     *                              @OA\Property(property="id", type="string", example="efbf49d9-78f4-436a-07ef-ca3aa661f9d7"),
     *                              @OA\Property(property="clothes_name", type="string", example="shirt A"),
     *                              @OA\Property(property="clothes_type", type="string", example="hat"),
     *                              @OA\Property(property="clothes_image", type="string", nullable=true, example="https://storage.googleapis.com/example.jpg"),
     *                              @OA\Property(property="clothes_category", type="string", example="head"),
     *                              @OA\Property(property="total", type="integer", example=6),
     *                              @OA\Property(property="last_used", type="string", example="2025-01-10 14:30:40")
     *                          )
     *                      )
     *                  ),
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="day", type="string", example="Mon"),
     *                      @OA\Property(property="clothes", type="array", nullable=true, example=null)
     *                  ),
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="day", type="string", example="Tue"),
     *                      @OA\Property(property="clothes", type="array", nullable=true, example=null)
     *                  ),
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="day", type="string", example="Wed"),
     *                      @OA\Property(property="clothes", type="array", nullable=true, example=null)
     *                  ),
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="day", type="string", example="Thu"),
     *                      @OA\Property(property="clothes", type="array", nullable=true, example=null)
     *                  ),
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="day", type="string", example="Fri"),
     *                      @OA\Property(property="clothes", type="array", nullable=true, example=null)
     *                  ),
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="day", type="string", example="Sat"),
     *                      @OA\Property(property="clothes", type="array", nullable=true, example=null)
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
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function get_stats_most_used_clothes_daily(Request $request){
        try {
            $user_id = $request->user()->id;

            $res = [];
            $days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
            foreach ($days as $dt) {
                $res_day = ClothesModel::getMostUsedClothesByDayAndType($user_id, $dt);
                $res[] = [
                    'day' => $dt,
                    'clothes' => count($res_day) > 0 ? $res_day : null
                ];
            }
            
            return response()->json([
                'status' => 'success',
                'message' => Generator::getMessageTemplate("fetch", 'stats'),
                'data' => $res,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
