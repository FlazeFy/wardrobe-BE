<?php

namespace App\Http\Controllers\Api\FeedbackApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

// Models
use App\Models\FeedbackModel;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Validation;

class Commands extends Controller
{
    /**
     * @OA\POST(
     *     path="/api/v1/feedback",
     *     summary="Add feedback",
     *     tags={"Feedback"},
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
            $validator = Validation::getValidateFeedback($request);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $user_id = $request->user()->id;

                $res = FeedbackModel::create([
                    'id' => Generator::getUUID(),
                    'feedback_rate' => $request->feedback_rate,
                    'feedback_body' => $request->feedback_body,
                    'created_at' => date("Y-m-d H:i:s"),
                    'created_by' => $user_id
                ]);

                if($res){
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("create", "feedback"),
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
