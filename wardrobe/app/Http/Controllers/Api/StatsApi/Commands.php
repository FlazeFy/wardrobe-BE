<?php

namespace App\Http\Controllers\Api\StatsApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

// Models
use App\Models\ClothesModel;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Validation;

class Commands extends Controller
{
    /**
     * @OA\POST(
     *     path="/api/v1/stats/clothes/by/{ctx}",
     *     summary="Get stats clothes by context (column)",
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
            // Validator
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

            $user_id = $request->user()->id;
            $final_res = [];

            // Query
            foreach ($list_ctx as $ctx) {
                $rows = ClothesModel::selectRaw("REPLACE(CONCAT(UPPER(SUBSTRING($ctx, 1, 1)), LOWER(SUBSTRING($ctx, 2))), '_', ' ') as context, COUNT(1) as total")
                    ->where('created_by', $user_id)
                    ->where($ctx,'!=','')
                    ->whereNotNull($ctx)
                    ->groupBy($ctx)
                    ->orderBy('total', 'desc')
                    ->limit(7)
                    ->get();

                if (count($list_ctx) > 1) {
                    $final_res[$ctx] = $rows;
                } else {
                    $final_res = $rows;
                }
            }

            // Response
            if (count($final_res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $final_res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
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
