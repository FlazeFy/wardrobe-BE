<?php

namespace App\Http\Controllers\Api\StatsApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
use App\Models\FeedbackModel;

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
                'message' => $e->getMessage(),
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

    /**
     * @OA\GET(
     *     path="/api/v1/stats/calendar/{month}/{year}",
     *     summary="Get calendar history for used history, weekly schedule, wash history, buyed history, and add to wardrobe",
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
                $query_select = "clothes.id, clothes_name, clothes_category, clothes_type";
                $user_id = $request->user()->id;

                $startDate = new DateTime("$year-$month-01");
                $endDate = clone $startDate;
                $endDate->modify('first day of next month');
                $interval = new DateInterval('P1D'); // End date is first day of next month
                $datePeriod = new DatePeriod($startDate, $interval, $endDate);       

                $res_used_history = ClothesUsedModel::selectRaw("$query_select, clothes_used.created_at")
                    ->join('clothes', 'clothes_used.clothes_id', '=', 'clothes.id')
                    ->where('clothes_used.created_by', $user_id)
                    ->whereYear('clothes_used.created_at', '=', $year)
                    ->whereMonth('clothes_used.created_at', '=', $month)
                    ->orderby('clothes_used.created_at', 'asc')
                    ->get();

                $res_wash_schedule = WashModel::selectRaw("$query_select, wash.created_at")
                    ->join('clothes', 'clothes.id', '=', 'wash.clothes_id')
                    ->where('wash.created_by', $user_id)
                    ->whereYear('wash.created_at', '=', $year)
                    ->whereMonth('wash.created_at', '=', $month)
                    ->orderby('wash.created_at', 'asc')
                    ->get();

                $res_weekly_schedule = ScheduleModel::selectRaw("$query_select, day")
                    ->join('clothes', 'clothes.id', '=', 'schedule.clothes_id')
                    ->where('schedule.created_by', $user_id)
                    ->get();

                $res_buyed_history = ClothesModel::selectRaw("$query_select, clothes_buy_at as created_at")
                    ->where('created_by', $user_id)
                    ->whereNotNull('clothes_buy_at')
                    ->whereYear('clothes_buy_at', '=', $year)
                    ->whereMonth('clothes_buy_at', '=', $month)
                    ->orderby('clothes_buy_at', 'asc')
                    ->get();

                $res_add_wardrobe = ClothesModel::selectRaw("$query_select, created_at")
                    ->where('created_by', $user_id)
                    ->whereYear('created_at', '=', $year)
                    ->whereMonth('created_at', '=', $month)
                    ->orderby('created_at', 'asc')
                    ->get();

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
                        if(date($format_date,strtotime($dt->clothes_buy_at)) == $date){
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
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
