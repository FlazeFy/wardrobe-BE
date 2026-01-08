<?php

namespace App\Http\Controllers\Api\ScheduleApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\Notification;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;

// Models
use App\Models\ClothesModel;
use App\Models\UserModel;
use App\Models\ScheduleModel;
// Helpers
use App\Helpers\Generator;
use App\Helpers\Validation;
use App\Helpers\Firebase;
use App\Helpers\Broadcast;

class Commands extends Controller
{
    private $module;

    public function __construct()
    {
        $this->module = "schedule";
    }

    /**
     * @OA\POST(
     *     path="/api/v1/clothes/schedule",
     *     summary="Post Create Schedule",
     *     description="This request is used to create schedule of clothes that will be used in the future by giving `clothes_id`, `schedule_note`, `day`, and `is_remind`. This request interacts with the MySQL database, and has a protected routes.",
     *     tags={"Clothes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"clothes_id", "day", "is_remind"},
     *             @OA\Property(property="clothes_id", type="string", example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9"),
     *             @OA\Property(property="schedule_note", type="string", nullable=true, example="daily uniform"),
     *             @OA\Property(property="day", type="string", example="sun"),
     *             @OA\Property(property="is_remind", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="clothes created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="schedule created")
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
     *         response=422,
     *         description="schedule failed to validated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="[failed validation message]")
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
    public function postCreateSchedule(Request $request){
        try{
            // Validate request body
            $validator = Validation::getValidateSchedule($request,'create');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $user_id = $request->user()->id;
                $clothes_id = $request->clothes_id;
                $day = $request->day;

                // Check schedule day availability for specific clothes
                $check_availability = ScheduleModel::checkDayAvailability($day, $clothes_id, $user_id);
                if($check_availability){
                    // Create schedule
                    $res = ScheduleModel::createSchedule([
                        'clothes_id' => $clothes_id,
                        'day' => $day,
                        'schedule_note' => $request->schedule_note,
                        'is_remind' => $request->is_remind
                    ], $user_id);
                    if($res){
                        // Get user social by ID
                        $user = UserModel::getSocial($user_id);
                        if($user->firebase_fcm_token){
                            // Get clothes by ID
                            $clothes = ClothesModel::getClothesById($clothes_id, $user_id);

                            // Broadcast FCM notification
                            $msg_body = "Your clothes called '$clothes->clothes_name' has been added to weekly schedule and set to wear on every $day";
                            Firebase::sendNotif($user->firebase_fcm_token, $msg_body, $user->username, $clothes_id);
                        }

                        // Return success response
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("create", $this->module),
                            'data' => $res
                        ], Response::HTTP_CREATED);
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => Generator::getMessageTemplate("unknown_error", null),
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }  
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("conflict", "day"),
                    ], Response::HTTP_CONFLICT);
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
     * @OA\DELETE(
     *     path="/api/v1/clothes/destroy_schedule/{id}",
     *     summary="Permanently Delete Schedule By ID",
     *     description="This request is used to permanently delete schedule by given `id`. This request interacts with the MySQL database, and has a protected routes.",
     *     tags={"Clothes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="schedule ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="schedule permanently deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="schedule permanently deleted")
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
    public function hardDeleteScheduleByID(Request $request, $id){
        try{
            $user_id = $request->user()->id;

            // Hard delete schedule by ID
            $rows = ScheduleModel::hardDeleteScheduleById($id, $user_id);
            if($rows > 0){
                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("permanently delete", $this->module),
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
