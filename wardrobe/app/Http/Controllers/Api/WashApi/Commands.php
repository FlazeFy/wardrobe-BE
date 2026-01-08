<?php

namespace App\Http\Controllers\Api\WashApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\Notification;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;

// Models
use App\Models\ClothesModel;
use App\Models\WashModel;
// Helpers
use App\Helpers\Generator;
use App\Helpers\Validation;
use App\Helpers\Audit;
use App\Helpers\Firebase;

class Commands extends Controller
{
    private $module;

    public function __construct()
    {
        $this->module = "wash";
    }

    /**
     * @OA\POST(
     *     path="/api/v1/clothes/wash",
     *     summary="Post Create Clothes Wash",
     *     description="This request is used to create wash history. This request interacts with the MySQL database, has a protected routes, and audited activity (history).",
     *     tags={"Wash"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"wash_checkpoint","clothes_id","wash_type"},
     *             @OA\Property(property="clothes_id", type="string", example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9"),
     *             @OA\Property(property="wash_type", type="string", example="laundry"),
     *             @OA\Property(property="wash_note", type="string", example="lorem ipsum"),
     *             @OA\Property(
     *                 property="wash_checkpoint", type="array",
     *                 @OA\Items(
     *                     type="object", required={"id","checkpoint_name","is_finished"},
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="checkpoint_name", type="string", example="Soak"),
     *                     @OA\Property(property="is_finished", type="boolean", example=false)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="clothes wash created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes wash history created")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Data is already exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="clothes is already at wash")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="{validation_msg}",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="{field validation message}")
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
    public function postCreateWash(Request $request){
        try{
            $user_id = $request->user()->id;

            // Validate request body
            $validator = Validation::getValidateWash($request,'create');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $clothes_id = $request->clothes_id;

                // Get clothes by ID
                $clothes = ClothesModel::getClothesById($clothes_id, $user_id);
                if($clothes){
                    // Check if clothes is on wash or not
                    $is_exist = WashModel::getActiveWash($clothes_id,$user_id);
                    if($is_exist){
                        return response()->json([
                            'status' => 'failed',
                            'message' => Generator::getMessageTemplate("custom", "clothes is still at wash"),
                        ], Response::HTTP_CONFLICT);
                    } else {
                        // Create wash
                        $res = WashModel::createWash([
                            'wash_note' => $request->wash_note, 
                            'clothes_id' => $clothes_id, 
                            'wash_type' => $request->wash_type, 
                            'wash_checkpoint' => $request->wash_checkpoint
                        ], $user_id); 
                        if($res){
                            // Create history
                            Audit::createHistory('Wash', $clothes->clothes_name, $user_id);
    
                            // Return success response
                            return response()->json([
                                'status' => 'success',
                                'message' => Generator::getMessageTemplate("create", 'clothes wash history'),
                            ], Response::HTTP_CREATED);
                        } else {
                            return response()->json([
                                'status' => 'error',
                                'message' => Generator::getMessageTemplate("unknown_error", null),
                            ], Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", $this->module),
                    ], Response::HTTP_NOT_FOUND);
                }
            }           
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/clothes/update_checkpoint/{id}",
     *     summary="Put Update Clothes Wash Checkpoint By ID",
     *     description="This request is used to update clothes wash checkpoint by given `id`. This request interacts with the MySQL database, and has a protected routes.",
     *     tags={"Wash"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="clothes ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"wash_checkpoint"},
     *             @OA\Property(
     *                 property="wash_checkpoint", type="array",
     *                 @OA\Items(
     *                     type="object", required={"id","checkpoint_name","is_finished"},
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="checkpoint_name", type="string", example="Soak"),
     *                     @OA\Property(property="is_finished", type="boolean", example=false)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="clothes wash updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes wash updated")
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
     *         description="clothes failed to updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="clothes wash not found")
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
    public function putUpdateWashByClothesID(Request $request, $id){
        try{
            $user_id = $request->user()->id;

            // Update wash by ID
            $res = WashModel::updateWashById(['wash_checkpoint' => $request->wash_checkpoint], $id, $user_id);
            if($res > 0){ 
                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("update", 'clothes wash'),
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'clothes wash'),
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
     * @OA\DELETE(
     *     path="/api/v1/clothes/destroy_wash/{id}",
     *     summary="Permanently Delete Wash By ID",
     *     description="This request is used to permanently delete wash history by given `id`. This request interacts with the MySQL database, and has a protected routes.",
     *     tags={"Wash"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="clothes wash ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="clothes wash permanently deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes wash permanently deleted")
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
     *         description="clothes wash not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="clothes wash not found")
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
    public function hardDeleteWashByID(Request $request, $id){
        try {
            $user_id = $request->user()->id;

            // Hard delete wash by ID
            $rows = WashModel::hardDeleteWashById($id, $user_id);
            if($rows > 0){
                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("permanently delete", 'clothes wash'),
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'clothes wash'),
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
