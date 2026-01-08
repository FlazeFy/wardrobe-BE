<?php

namespace App\Http\Controllers\Api\ScheduleApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

// Models
use App\Models\ScheduleModel;
// Helpers
use App\Helpers\Generator;

class Queries extends Controller
{
    private $module;

    public function __construct()
    {
        $this->module = "schedule";
    }

    /**
     * @OA\GET(
     *     path="/api/v1/clothes/schedule/{day}",
     *     summary="Get Clothes Schedule By Day",
     *     description="This request is used to get schedule by given `day` name. This request interacts with the MySQL database, and has a protected routes",
     *     tags={"Clothes"},
     *     security={{"bearerAuth":{}}},
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
    public function getScheduleByDay(Request $request, $day){
        try { 
            $user_id = $request->user()->id;

            // Get schedule by day
            $res = ScheduleModel::getScheduleByDay($day,$user_id);
            if($res && count($res) > 0) {        
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
     *     path="/api/v1/clothes/schedule/tomorrow/{day}",
     *     summary="Get Tomorrow Schedule",
     *     description="This request is used to get the schedule for tomorrow and two days later from the outfit and clothes schedule. This request interacts with the MySQL database, and has a protected routes.",
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
    public function getScheduleTomorrow(Request $request, $day){
        try { 
            $user_id = $request->user()->id;
            $tomorrow = date('D', strtotime("next $day +1 day"));
            $two_days_later = date('D', strtotime("next $day +2 day"));

            // Get schedule by day
            $res_tomorrow = ScheduleModel::getScheduleByDay($tomorrow, $user_id);
            $res_2_days_later = ScheduleModel::getScheduleByDay($two_days_later, $user_id);

            // Return success response
            return response()->json([
                'status' => 'success',
                'message' => Generator::getMessageTemplate("fetch", $this->module),
                'data' => [
                    'tomorrow' => count($res_tomorrow) > 0 ? $res_tomorrow : null,
                    'tomorrow_day' => $tomorrow,
                    'two_days_later' => count($res_2_days_later) > 0 ? $res_2_days_later : null,
                    'two_days_later_day' => $two_days_later
                ]
            ], Response::HTTP_OK);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}