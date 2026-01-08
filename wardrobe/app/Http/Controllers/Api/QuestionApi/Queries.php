<?php

namespace App\Http\Controllers\Api\QuestionApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

// Models
use App\Models\QuestionModel;
// Helpers
use App\Helpers\Generator;

class Queries extends Controller
{
    private $module;
    
    public function __construct()
    {
        $this->module = "question";
    }

    /**
     * @OA\GET(
     *     path="/api/v1/question/faq",
     *     summary="Get Showing FAQ",
     *     description="This request is used to get showing FAQ in the welcome page (Maximum to fetch 8 item). This request interacts with the MySQL database.",
     *     tags={"Question"},
     *     @OA\Response(
     *         response=200,
     *         description="FAQ fetched successfully. Ordered in descending order by `created_at`",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="faq fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="question", type="string", example="How to login?"),
     *                         @OA\Property(property="answer", type="string", example="Using your username and password"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-20 22:53:47"),
     *                     )
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="faq failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="faq not found")
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
    public function getQuestionFAQ(){
        try{
            // Get FAQ (question)
            $res = QuestionModel::getFAQ();
            if ($res && count($res) > 0) {
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
}
