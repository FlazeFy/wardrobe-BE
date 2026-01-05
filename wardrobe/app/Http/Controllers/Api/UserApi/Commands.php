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
   /**
     * @OA\POST(
     *     path="/api/v1/user/fcm",
     *     summary="Update user FCM token",
     *     description="Update user's Firebase Cloud Messaging token from mobile. This request is using MySQL database.",
     *     tags={"User"},
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

    public function update_user_fcm(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            // Validator
            $validator = Validation::getValidateUser($request,'update_fcm');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                // Service : Update
                $rows = UserModel::where('id',$user_id)->update([
                    'firebase_fcm_token' => $request->firebase_fcm_token,
                ]);

                // Respond
                if($rows > 0){
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("update", 'fcm'),
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
