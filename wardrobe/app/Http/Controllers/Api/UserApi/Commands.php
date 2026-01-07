<?php

namespace App\Http\Controllers\Api\UserApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

// Models
use App\Models\UserModel;
// Helper
use App\Helpers\Validation;
use App\Helpers\Generator;

class Commands extends Controller
{
    private $module;

    public function __construct()
    {
        $this->module = "profile";
    }

   /**
     * @OA\POST(
     *     path="/api/v1/user/fcm",
     *     summary="Put Update User FCM Token",
     *     description="This request is used to update user's Firebase Cloud Messaging token from mobile. This request interacts with the MySQL database, and has a protected routes",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"firebase_fcm_token"},
     *             @OA\Property(property="firebase_fcm_token", type="string", example="123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="fcm updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="fcm updated")
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
     *                     @OA\Property(property="message", type="string", example="firebase_fcm_token must be less than 255 characters")
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="status", type="string", example="failed"),
     *                     @OA\Property(property="message", type="string", example="firebase_fcm_token is a required field")
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
     *     )
     * )
     */

    public function updateUserFcm(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            // Validate request body
            $validator = Validation::getValidateUser($request,'update_fcm');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                // Update user by id
                $rows = UserModel::updateUserById(['firebase_fcm_token' => $request->firebase_fcm_token],$user_id);
                if($rows > 0){
                    // Return success response
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("update", $this->module),
                    ], Response::HTTP_OK);
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
