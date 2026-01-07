<?php

namespace App\Http\Controllers\Api\UserApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

// Models
use App\Models\UserModel;
use App\Models\AdminModel;
// Helpers
use App\Helpers\Generator;

class Queries extends Controller
{
    private $module;

    public function __construct()
    {
        $this->module = "profile";
    }

    /**
     * @OA\GET(
     *     path="/api/v1/user/my",
     *     summary="Get My Profile",
     *     description="This request is used to get user profile. This request interacts with the MySQL database, and has a protected routes",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profile fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="profile fetched"),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="username", type="string", example="flazefy"),
     *                  @OA\Property(property="email", type="string", example="flazen.edu@gmail.com"),
     *                  @OA\Property(property="telegram_user_id", type="string", example="123456789"),
     *                  @OA\Property(property="telegram_is_valid", type="integer", example="1"),
     *                  @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-20 22:53:47"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-20 22:53:47")   
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
     *         description="profile failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="profile not found")
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
    public function getMyProfile(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            // Get user profile by role
            $res = AdminModel::getSocial($user_id);
            if($res === null){
                $res = UserModel::getSocial($user_id);
            }

            if($res) {
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
     *     path="/api/v1/user/my_year",
     *     summary="Get Available Year",
     *     description="This request is used to get all year found on content (clothes, outfit, wash, schedule). This request interacts with the MySQL database, and has a protected routes",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Year filter fetched successfully. Ordered in descending order by `year`",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="year filter fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="year", type="integer", example=2024)
     *                  )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="year failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="year filter not found")
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
    public function getMyAvailableYearFilter(Request $request){
        try{
            $user_id = $request->user()->id;

            // Get user's year by the content they post
            $res = UserModel::getMyAvailableYearFilter($user_id);
            if($res && count($res) > 0){
                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'year filter'),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'year filter'),
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
