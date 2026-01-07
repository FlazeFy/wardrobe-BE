<?php

namespace App\Http\Controllers\Api\FeedbackApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

// Models
use App\Models\FeedbackModel;
use App\Models\UserModel;
// Helpers
use App\Helpers\Generator;
use App\Helpers\Firebase;
use App\Helpers\Validation;

class Commands extends Controller
{
    private $module;
    public function __construct()
    {
        $this->module = "feedback";
    }

    /**
     * @OA\POST(
     *     path="/api/v1/feedback",
     *     summary="Post Create Feedback",
     *     tags={"Feedback"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"feedback_rate", "feedback_body"},
     *             @OA\Property(property="feedback_rate", type="integer", example=4),
     *             @OA\Property(property="feedback_body", type="string", example="cool apps")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="feedback created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="feedback created")
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
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="status", type="string", example="failed"),
     *                     @OA\Property(property="message", type="string", example="feedback body must be at least 2 characters")
     *                 )
     *             }
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
    public function post_feedback(Request $request)
    {
        try{
            // Validate request body
            $validator = Validation::getValidateFeedback($request);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $user_id = $request->user()->id;

                // Create feedback
                $res = FeedbackModel::createFeedback(['feedback_rate' => $request->feedback_rate, 'feedback_body' => $request->feedback_body], $user_id);
                if($res){
                    // Get user social data by id
                    $user = UserModel::getSocial($user_id);

                    if($user->firebase_fcm_token){
                        // Broadcast firebase notification
                        $msg_body = "Thank you for your feedback! We appreciate your time and effort in helping us improve. Your thoughts is valuable, and we'll use it to make things even better!";
                        Firebase::sendNotif($user->firebase_fcm_token, $msg_body, $user->username, $res->id);
                    }

                    // Return success response
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("create", $this->module),
                    ], Response::HTTP_CREATED);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => Generator::getMessageTemplate("unknown_error", null),
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
