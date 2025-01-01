<?php

namespace App\Http\Controllers\Api\AuthApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

// Models
use App\Models\UserModel;
use App\Models\AdminModel;

// Helpers
use App\Helpers\Generator;

class Queries extends Controller
{
     /**
     * @OA\GET(
     *     path="/api/v1/logout",
     *     summary="Sign out from Apps",
     *     tags={"Auth"},
     *     @OA\Response(
     *         response=200,
     *         description="Logout success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Logout success"),
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
    public function logout(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $check = AdminModel::where('id', $user_id)->first();

            if($check == null){
                $request->user()->currentAccessToken()->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("custom", 'logout success')
                ], Response::HTTP_OK);
            } else {
                // Admin
                $request->user()->currentAccessToken()->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("custom", 'logout success')
                ], Response::HTTP_OK);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
