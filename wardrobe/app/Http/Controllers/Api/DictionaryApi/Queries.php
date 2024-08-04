<?php

namespace App\Http\Controllers\Api\DictionaryApi;
use App\Http\Controllers\Controller;

// Models
use App\Models\DictionaryModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/dct/{type}",
     *     summary="Show dictionary by type",
     *     tags={"Dictionary"},
     *     @OA\Response(
     *         response=200,
     *         description="dictionary found"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="dictionary failed to fetch"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function get_dct_by_type(Request $request, $type)
    {
        try{
            $user_id = $request->user()->id;

            $res = DictionaryModel::select('dictionary_name')
                ->where('dictionary_type',$type)
                ->orderBy('dictionary_name', 'ASC')
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'dictionary fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'dictionary failed to fetched',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. Please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
