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
    private $module;
    
    public function __construct()
    {
        $this->module = "question";
    }

   /**
     * @OA\POST(
     *     path="/api/v1/question",
     *     summary="Post Create Question",
     *     description="This request is used to create question. This request interacts with the MySQL database, and has a protected routes",
     *     tags={"Question"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"question"},
     *             @OA\Property(property="question", type="string", example="lorem ipsum?")
     *         )
     *     ),
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

    public function postQuestion(Request $request)
    {
        try{
            // Validator request body
            $validator = Validation::getValidateQuestion($request,'create');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $user_id = $request->user()->id;
                
                // Create question
                $rows = QuestionModel::createQuestion(['question' => $request->question, 'answer' => null], $user_id);
                if($rows){
                    // Return success response
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("create", $this->module),
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
