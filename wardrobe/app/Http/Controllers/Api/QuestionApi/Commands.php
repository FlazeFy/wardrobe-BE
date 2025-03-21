<?php

namespace App\Http\Controllers\Api\QuestionApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

// Models
use App\Models\QuestionModel;

// Helper
use App\Helpers\Validation;
use App\Helpers\Generator;

class Commands extends Controller
{
   /**
     * @OA\POST(
     *     path="/api/v1/question",
     *     summary="Post question",
     *     description="Create a new question. This request is using MySQL database.",
     *     tags={"Question"},
     *     @OA\Response(
     *         response=201,
     *         description="question created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="question created")
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
     *                     @OA\Property(property="message", type="string", example="question name must be at least 2 characters")
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="status", type="string", example="failed"),
     *                     @OA\Property(property="message", type="string", example="question is a required field")
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

    public function post_question(Request $request)
    {
        try{
            // Validator
            $validator = Validation::getValidateQuestion($request,'create');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                // Service : Create
                $rows = QuestionModel::create([
                    'id' => Generator::getUUID(),
                    'question' => $request->question,
                    'answer' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'is_show' => 0
                ]);

                // Respond
                if($rows){
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("create", 'question'),
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
