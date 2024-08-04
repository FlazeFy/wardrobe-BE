<?php

namespace App\Http\Controllers\Api\AuthApi;

use App\Models\UserModel;
use App\Models\AdminModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class Queries extends Controller
{
     /**
     * @OA\GET(
     *     path="/api/v1/logout",
     *     summary="Sign out from Apps",
     *     tags={"Auth"},
     *     @OA\Response(
     *         response=200,
     *         description="Logout success"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function logout(Request $request)
    {
        $user_id = $request->user()->id;
        $check = AdminModel::where('id', $user_id)->first();

        if($check == null){
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'message' => 'logout success'
            ], Response::HTTP_OK);
        } else {
            // Admin
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'message' => 'logout success'
            ], Response::HTTP_OK);
        }
    }
}
