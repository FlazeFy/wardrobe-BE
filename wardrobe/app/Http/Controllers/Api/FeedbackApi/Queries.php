<?php

namespace App\Http\Controllers\Api\FeedbackApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

// Models
use App\Models\FeedbackModel;
use App\Models\AdminModel;
// Helpers
use App\Helpers\Generator;

class Queries extends Controller
{
    private $module;
    
    public function __construct()
    {
        $this->module = "feedback";
    }

    /**
     * @OA\GET(
     *     path="/api/v1/feedback",
     *     summary="Get All Feedback",
     *     description="This request is used to get all feedback when user use the App. This request is using MySql database, have a protected routes, and has a pagination.",
     *     tags={"Feedback"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Feedback fetched successfully. Ordered in descending order by `created_at`",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="feedback fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", example="6f59235e-c398-8a83-2f95-3f1fbe95ca6e"),
     *                         @OA\Property(property="feedback_rate", type="integer", example=5),
     *                         @OA\Property(property="feedback_body", type="string", example="Lorem ipsum"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-20 22:53:47"),
     *                         @OA\Property(property="created_by", type="string", example="flazefy")
     *                     )
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
     *         description="feedback failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="feedback not found")
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
    public function getAllFeedback(Request $request){
        try{
            $user_id = $request->user()->id;
            $paginate = $request->query('per_page_key') ?? 14;

            // Make sure only admin can access the request
            $check_admin = AdminModel::find($user_id);
            if($check_admin){
                // Get all feedback
                $res = FeedbackModel::getAll($paginate);
                if ($res && count($res) > 0) {
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
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("permission", 'admin'),
                ], Response::HTTP_UNAUTHORIZED);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
