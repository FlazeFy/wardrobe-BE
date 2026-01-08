<?php

namespace App\Http\Controllers\Api\OutfitApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\Notification;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;

// Models
use App\Models\ClothesModel;
use App\Models\UserModel;
use App\Models\ClothesUsedModel;
use App\Models\OutfitModel;
use App\Models\OutfitRelModel;
use App\Models\OutfitUsedModel;
// Helpers
use App\Helpers\Generator;
use App\Helpers\Validation;
use App\Helpers\Firebase;
use App\Helpers\Broadcast;

class Commands extends Controller
{
    private $module;

    public function __construct()
    {
        $this->module = "outfit";
    }

    /**
     * @OA\POST(
     *     path="/api/v1/clothes/save/outfit",
     *     summary="Post Create Outfit",
     *     description="This request is used to create an outfit that will contain multiple set of clothes. This request interacts with the MySQL database, broadcast with Firebase FCM & Telegram, has a protected routes, and audited activity (history).",
     *     tags={"Outfit"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"list_outfit"},
     *             @OA\Property(
     *                 property="list_outfit", type="array",
     *                 description="List of outfits to be created along with their clothes",
     *                 @OA\Items(
     *                     type="object", required={"outfit_name","data"},
     *                     @OA\Property(property="outfit_name", type="string", example="Daily Office Outfit"),
     *                     @OA\Property(
     *                         property="data", type="array", description="List of clothes attached to the outfit",
     *                         @OA\Items(
     *                             type="object", required={"id","clothes_name"},
     *                             @OA\Property(property="id", type="string", example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9"),
     *                             @OA\Property(property="clothes_name", type="string", example="Black Pants")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="outfit created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="outfit created with 2 clothes attached")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Data is already exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="outfit is already exist")
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
    public function postCreateOutfit(Request $request){
        try{
            $user_id = $request->user()->id;
            $outfits = $request->list_outfit;

            if(count($outfits) > 0){
                $success_outfit = 0;
                $failed_outfit = 0;
                $success_rel_outfit = 0;
                $failed_rel_outfit = 0;
                $message_outfit = "";

                foreach ($outfits as $idx => $dt) {
                    // Create outfit
                    $outfit = OutfitModel::createOutfit([
                        'outfit_name' => $dt['outfit_name'], 
                        'outfit_note' => null, 
                        'is_favorite' => 0, 
                        'is_auto' => 1
                    ], $user_id);
                    if($outfit){
                        $success_outfit++;
                        $message_outfit .= ($idx+1).". ".$dt['outfit_name']."\n";

                        // Iterate to attach every clothes selected to newly outfit
                        foreach ($dt['data'] as $clothes) {
                            // Create outfit relation
                            $outfit_rel = OutfitRelModel::createOutfitRel(['outfit_id' => $outfit->id, 'clothes_id' => $clothes['id']], $user_id);
                            
                            $message_outfit .= $clothes['clothes_name'].", ";
                            if($outfit_rel){
                                $success_rel_outfit++;
                            } else {
                                $failed_rel_outfit++;
                            }
                        }
                        $message_outfit .= "\n\n";
                    } else {
                        $failed_outfit++;
                    }
                }

                if($success_rel_outfit > 0 && $success_outfit > 0){
                    $message = "Hello, $user->username. You have successfully add $success_outfit outfit. Here's the detail :\n\n$message_outfit";

                    // Get user social by ID
                    $user = UserModel::getSocial($user_id);
                    if($user && $user->telegram_user_id && $user->telegram_is_valid === 1){
                        Broadcast::sendTelegramMessage($user->telegram_user_id, $message);
                    }
                    if($user && $user->firebase_fcm_token){
                        // Broadcast FCM notification
                        Firebase::sendNotif($user->firebase_fcm_token, $message, $user->username, null);
                    }
                }

                if($failed_rel_outfit == 0){
                    // Return success response
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("custom", "outfit created with $success_rel_outfit clothes attached"),
                    ], Response::HTTP_CREATED);
                } else if($failed_rel_outfit > 0 && $success_rel_outfit > 0){
                    // Return success response
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("custom", "outfit created with $success_rel_outfit clothes attached, but there is $failed_rel_outfit clothes failed to add"),
                    ], Response::HTTP_CREATED);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => Generator::getMessageTemplate("unknown_error", null),
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("custom", 'at least one outfit must be selected'),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/clothes/outfit/history/save",
     *     summary="Post Create Outfit Used History",
     *     description="This request is used to create outfit used history. This request interacts with the MySQL database, broadcast with Firebase FCM, and has a protected routes.",
     *     tags={"Outfit"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"outfit_id","used_context"},
     *             @OA\Property(property="outfit_id", type="string", example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9"),
     *             @OA\Property(property="used_context", type="string", example="work")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="outfit history created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="outfit history created with 2 clothes attached")
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
     *         description="outfit history not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="outfit not found")
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
    public function postCreateOutfitHistory(Request $request){
        try{
            $user_id = $request->user()->id;
            $outfit_id = $request->outfit_id;

            // Get outfit by ID
            $outfit = OutfitModel::getOutfitById($outfit_id, $user_id);
            if (!$outfit) {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'outfit'),
                ], Response::HTTP_NOT_FOUND);
            } else {
                // Create outfit used
                $res = OutfitUsedModel::createOutfitUsed($outfit_id, $user_id);
                if($res){
                    $success_clothes = 0;
                    $failed_clothes = 0;
                    $message_clothes = "";

                    // Get outfit relation with clothes
                    $list_clothes = OutfitRelModel::getClothesByOutfitID($outfit_id, $user_id);
                    foreach ($list_clothes as $dt) {
                        // Create clothes used
                        $clothes_history = ClothesUsedModel::createClothesUsed([
                            'clothes_id' => $dt->id, 
                            'clothes_note' => 'Part of outfit '.$outfit->outfit_name, 
                            'used_context' => $request->used_context
                        ], $user_id);

                        if($clothes_history){
                            $message_clothes .= "- $dt->clothes_name ($dt->clothes_type)\n";
                            $success_clothes++;
                        } else {
                            $failed_clothes++;
                        }
                    }

                    if($success_clothes > 0){
                        // Get user social by ID
                        $user = UserModel::getSocial($user_id);
                        if($user && $user->firebase_fcm_token){
                            // Broadcast FCM notification
                            $message = "Hello, $user->username. You have successfully add $success_clothes clothes to history of used. Here's the detail :\n\n$message_clothes";
                            Firebase::sendNotif($user->firebase_fcm_token, $message, $user->username, $outfit->id);
                        }
                    }
    
                    if($failed_clothes == 0){
                        // Return success response
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("custom", "outfit history created with $success_clothes clothes attached"),
                        ], Response::HTTP_CREATED);
                    } else if($failed_clothes > 0 && $success_clothes > 0){
                        // Return success response
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("custom", "outfit history created with $success_clothes clothes attached, but there is $failed_clothes clothes failed to add"),
                        ], Response::HTTP_CREATED);
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => Generator::getMessageTemplate("unknown_error", null),
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
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

    /**
     * @OA\POST(
     *     path="/api/v1/clothes/outfit/save/clothes",
     *     summary="Post Create Clothes Relation With Outfit",
     *     description="This request is used to create clothes relation with the outfit. This request interacts with the MySQL database, broadcast with Firebase FCM, and has a protected routes.",
     *     tags={"Outfit"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"outfit_id","clothes"},
     *             @OA\Property(property="outfit_id", type="string", example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9"),
     *             @OA\Property(
     *                 property="clothes", type="array",
     *                 @OA\Items(
     *                     type="object", required={"clothes_id","clothes_name","clothes_type"},
     *                     @OA\Property(property="clothes_id", type="string", example="uuid-clothes-1"),
     *                     @OA\Property(property="clothes_name", type="string", example="Black Pants"),
     *                     @OA\Property(property="clothes_type", type="string", example="pants")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="outfit created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="2 clothes attached")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="outfit not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="outfit not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Data is already exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="clothes is already exist")
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
     *         description="clothes outfit failed to validated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="[failed validation message]")
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
    public function postCreateClothesOutfit(Request $request){
        try{
            $user_id = $request->user()->id;
            $outfit_id = $request->outfit_id;

            // Check if outfit is exist
            $is_exist = OutfitModel::isExist($outfit_id, $user_id);
            if (!$is_exist) {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'outfit'),
                ], Response::HTTP_NOT_FOUND);
            } else {
                $message_clothes = "";
                $success_clothes = 0;
                $failed_clothes = 0;
                $clothes = json_decode($request->clothes);

                foreach($clothes as $idx => $dt) {
                    $request->merge([
                        'clothes_id' => $dt->clothes_id,
                        'clothes_name' => $dt->clothes_name,
                        'clothes_type' => $dt->clothes_type
                    ]);

                    // Validate request body
                    $validator = Validation::getValidateClothes($request,'create_outfit_relation');
                    if ($validator->fails()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => "At the $idx-th clothes : ".$validator->errors()
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
                    } else {
                        $clothes_id = $dt->clothes_id;

                        // Check if clothes already attached to the outfit
                        $is_exist_clothes = OutfitRelModel::isExistClothes($user_id, $clothes_id, $outfit_id);
                        if(!$is_exist_clothes){
                            // Create outfit relation
                            $outfit_rel = OutfitRelModel::createOutfitRel(['outfit_id' => $outfit_id, 'clothes_id' => $clothes_id], $user_id);
                            if($outfit_rel){
                                $message_clothes .= "- $dt->clothes_name ($dt->clothes_type)\n";
                                $success_clothes++;
                            } else {
                                $failed_clothes++;
                            }
                        } else {
                            $failed_clothes++;
                        }
                    }
                }

                if($success_outfit > 0){
                    // Get user social by ID
                    $user = UserModel::getSocial($user_id);
                    if($user->firebase_fcm_token){
                        // Get outfit by ID
                        $outfit = OutfitModel::getOutfitById($outfit_id, $user_id);

                        // Broadcast FCM notification
                        $msg_body = "There is a clothes changes in outfit's '$outfit->outfit_name'";
                        Firebase::sendNotif($user->firebase_fcm_token, $msg_body, $user->username, $outfit_id);
                    }
                }
    
                if($failed_clothes == 0){
                    // Return success response
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("custom", "$success_clothes clothes attached"),
                    ], Response::HTTP_CREATED);
                } else if($failed_clothes > 0 && $success_clothes > 0){
                    // Return success response
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("custom", "$success_clothes clothes attached, but there is $failed_clothes clothes failed to add"),
                    ], Response::HTTP_CREATED);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("custom", "nothing has change"),
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
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
     * @OA\DELETE(
     *     path="/api/v1/clothes/outfit/remove/{clothes_id}",
     *     summary="Permanently Delete (Remove) Clothes From Outfit Relation By Clothes ID",
     *     description="This request is used to delete (remove) clothes from outfit relation by given `clothes_id`. This request interacts with the MySQL database, broadcast with Firebase FCM, and has a protected routes.",
     *     tags={"Outfit"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="clothes_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="outfit history ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="clothes remove from outfit",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes removed")
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
     *         description="clothes not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="clothes not found")
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
    public function hardDeleteClothesOutfitByID(Request $request, $clothes_id, $outfit_id){
        try{
            $user_id = $request->user()->id;

            // Hard delete outfit relation
            $rows = OutfitRelModel::deleteRelation($user_id, $clothes_id, $outfit_id);
            if($rows > 0){
                // Get user social by ID
                $user = UserModel::getSocial($user_id);
                if($user->firebase_fcm_token){
                    // Get clothes and outfit by ID
                    $clothes = ClothesModel::getClothesById($clothes_id, $user_id);
                    $outfit = OutfitModel::getOutfitById($outfit_id, $user_id);
                    
                    // Broadcast FCM notification
                    $msg_body = "clothes '$clothes->clothes_name' has been removed from outfit '$outfit->outfit_name'";
                    Firebase::sendNotif($user->firebase_fcm_token, $msg_body, $user->username, "$clothes_id-$outfit_id");
                }

                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("remove", $this->module),
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
