<?php

namespace App\Http\Controllers\Api\StatsApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

// Models
use App\Models\ClothesModel;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Validation;

class Commands extends Controller
{
    private $module;
    public function __construct()
    {
        $this->module = "stats";
    }

    /**
     * @OA\POST(
     *     path="/api/v1/stats/clothes/by/{ctx}",
     *     summary="Get Stats Clothes By Context",
     *     description="This request is used to get summary stats. This request is using MySql database, have a protected routes",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="ctx",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Context / Column",
     *         example="clothes_merk",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="context", type="string", example="Main Room"),
     *                          @OA\Property(property="total", type="integer", example=2)
     *                 )
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
     *         description="stats failed to fetch",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="stats not found")
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
    public function get_stats_clothes_most_context(Request $request, $ctx)
    {
        try {
            // Define user ID if token attached
            if ($request->hasHeader('Authorization')) {
                $user = Auth::guard('sanctum')->user(); 
                $user_id = $user ? $user->id : null;
            } else {
                $user_id = null;
            }

            // Validate param (multiple context search)
            if (strpos($ctx, ",") !== false) {
                $list_ctx = explode(",", $ctx);
                foreach ($list_ctx as $dt) {
                    $request->merge(['context' => $dt]);
                    $validator = Validation::getValidateStats($request, 'most_context');
                    if ($validator->fails()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => $validator->errors()
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
                        break;
                    }
                }
            } else {
                // Validate param (single context search)
                $request->merge(['context' => $ctx]);
                $validator = Validation::getValidateStats($request, 'most_context');
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $validator->errors()
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                } else {
                    $list_ctx = [$ctx];
                }
            }

            // Query
            $final_res = [];
            foreach ($list_ctx as $ctx) {
                $context_stats = ClothesModel::getContextStats($ctx, $user_id);

                if (count($list_ctx) > 1) {
                    $final_res[$ctx] = $context_stats;
                } else {
                    $final_res = $context_stats;
                }
            }

            if (count($final_res) > 0) {
                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", $this->module),
                    'data' => $final_res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", $this->module),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
