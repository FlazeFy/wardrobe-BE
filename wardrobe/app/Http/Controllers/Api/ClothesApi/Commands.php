<?php

namespace App\Http\Controllers\Api\ClothesApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Messaging\Notification;
use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\Canvas\Factory as CanvasFactory;
use Dompdf\Options as DompdfOptions;
use Dompdf\Adapter\CPDF;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;

// Models
use App\Models\ClothesModel;
use App\Models\UserModel;
use App\Models\ClothesUsedModel;
use App\Models\WashModel;
use App\Models\ScheduleModel;
use App\Models\OutfitModel;
use App\Models\OutfitRelModel;
use App\Models\OutfitUsedModel;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Validation;
use App\Helpers\Audit;
use App\Helpers\Firebase;

// Jobs
use App\Jobs\ProcessMailer;

class Commands extends Controller
{
    private $max_size_file;
    private $allowed_file_type;

    public function __construct()
    {
        $this->max_size_file = 10000000; // 10 Mb
        $this->allowed_file_type = ['jpg','jpeg','gif','png'];
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/clothes/destroy/{id}",
     *     summary="Permentally delete clothes by id",
     *     tags={"Clothes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="clothes ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="clothes permentally deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes permentally deleted")
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
    public function hard_delete_clothes_by_id(Request $request, $id)
    {
        try {
            $user_id = $request->user()->id;

            $rows = ClothesModel::destroy($id);

            if($rows > 0){
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("permentally delete", 'clothes'),
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'clothes'),
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
     *     path="/api/v1/clothes/delete/{id}",
     *     summary="Delete clothes by id",
     *     tags={"Clothes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="clothes ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="clothes deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes deleted")
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
    public function soft_delete_clothes_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            $rows = ClothesModel::where('id', $id)
                ->where('created_by', $user_id)
                ->update([
                    'deleted_at' => date('Y-m-d H:i:s'),
            ]);

            if($rows > 0){                
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("delete", 'clothes'),
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'clothes'),
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
     * @OA\POST(
     *     path="/api/v1/clothes/history",
     *     summary="Add clothes history",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=201,
     *         description="clothes created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes created")
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
    public function post_history_clothes(Request $request)
    {
        try{
            $validator = Validation::getValidateClothesUsed($request,'create');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $user_id = $request->user()->id;

                $res = ClothesUsedModel::create([
                    'id' => Generator::getUUID(),
                    'clothes_id' => $request->clothes_id,
                    'clothes_note' => $request->clothes_note,
                    'used_context' => $request->used_context,
                    'created_at' => date("Y-m-d H:i:s"),
                    'created_by' => $user_id
                ]);

                if($res){
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("create", "clothes history"),
                        'data' => $res
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

    /**
     * @OA\POST(
     *     path="/api/v1/clothes/schedule",
     *     summary="Add schedule",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=201,
     *         description="clothes created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="schedule created")
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
    public function post_schedule(Request $request)
    {
        try{
            $validator = Validation::getValidateSchedule($request,'create');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $user_id = $request->user()->id;
                $clothes_id = $request->clothes_id;
                $day = $request->day;

                $check_availability = ScheduleModel::checkDayAvailability($day, $clothes_id, $user_id);

                if($check_availability){
                    $res = ScheduleModel::create([
                        'id' => Generator::getUUID(),
                        'clothes_id' => $clothes_id,
                        'day' => $day,
                        'schedule_note' => $request->schedule_note,
                        'is_remind' => $request->is_remind,
                        'created_at' => date("Y-m-d H:i:s"),
                        'created_by' => $user_id
                    ]);
    
                    if($res){
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("create", "schedule"),
                            'data' => $res
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
                        'message' => Generator::getMessageTemplate("conflict", "day"),
                    ], Response::HTTP_CONFLICT);
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
     *     summary="Update clothes wash by id",
     *     tags={"Clothes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="clothes ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
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
    public function update_wash_by_clothes_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            $res = WashModel::where('clothes_id',$id)
                ->where('created_by',$user_id)
                ->whereNull('finished_at')
                ->update([
                    'wash_checkpoint' => $request->wash_checkpoint,
                ]);

            if($res > 0){ 
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
     *     summary="Permentally delete wash by id",
     *     tags={"Clothes"},
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
     *         description="clothes wash permentally deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes wash permentally deleted")
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
    public function hard_delete_wash_by_id(Request $request, $id)
    {
        try {
            $user_id = $request->user()->id;
            $rows = WashModel::destroy($id);

            if($rows > 0){
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("permentally delete", 'clothes wash'),
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
     *     path="/api/v1/clothes/destroy_used/{id}",
     *     summary="Permentally delete clothes used by id",
     *     tags={"Clothes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="clothes used ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="clothes used permentally deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes used permentally deleted")
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
     *         description="clothes used not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="clothes used not found")
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
    public function hard_delete_clothes_used_by_id(Request $request, $id)
    {
        try {
            $user_id = $request->user()->id;
            $rows = ClothesUsedModel::where('id',$id)
                ->where('created_by',$user_id)
                ->delete();

            if($rows > 0){
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("permentally delete", 'clothes used'),
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'clothes used'),
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
     * @OA\POST(
     *     path="/api/v1/clothes",
     *     summary="Create clothes",
     *     tags={"Clothes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="clothes created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes created, its called product A")
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
    public function post_clothes(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $validator = Validation::getValidateClothes($request,'create');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $clothes_image = null;  
                if($request->clothes_image){
                    $clothes_image = $request->clothes_image;  
                } 
                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    if ($file->isValid()) {
                        $file_ext = $file->getClientOriginalExtension();
                        // Validate file type
                        if (!in_array($file_ext, $this->allowed_file_type)) {
                            return response()->json([
                                'status' => 'failed',
                                'message' => Generator::getMessageTemplate("custom", 'The file must be a '.implode(', ', $this->allowed_file_type).' file type'),
                            ], Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                        // Validate file size
                        if ($file->getSize() > $this->max_size_file) {
                            return response()->json([
                                'status' => 'failed',
                                'message' => Generator::getMessageTemplate("custom", 'The file size must be under '.($this->max_size_file/1000000).' Mb'),
                            ], Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
        
                        // Helper: Upload clothes image
                        try {
                            $user = UserModel::find($user_id);
                            $clothes_image = Firebase::uploadFile('clothes', $user_id, $user->username, $file, $file_ext); 
                        } catch (\Exception $e) {
                            return response()->json([
                                'status' => 'error',
                                'message' => $e->getMessage(),
                            ], Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    }
                }

                $is_exist = ClothesModel::selectRaw('1')
                    ->where('clothes_name',$request->clothes_name)
                    ->where('created_by',$user_id)
                    ->first();

                if(!$is_exist){
                    $id = Generator::getUUID();
                    $res = ClothesModel::create([
                        'id' => $id,                 
                        'clothes_name' => $request->clothes_name, 
                        'clothes_category' => $request->clothes_category, 
                        'clothes_desc' => $request->clothes_desc, 
                        'clothes_merk' => $request->clothes_merk, 
                        'clothes_color' => $request->clothes_color, 
                        'clothes_price' => $request->clothes_price, 
                        'clothes_image' => $clothes_image, 
                        'clothes_size' => $request->clothes_size,  
                        'clothes_gender' => $request->clothes_gender,  
                        'clothes_made_from' => $request->clothes_made_from,  
                        'clothes_type' => $request->clothes_type,  
                        'clothes_buy_at' => $request->clothes_buy_at,  
                        'clothes_qty' => $request->clothes_qty, 
                        'is_faded' => $request->is_faded,  
                        'has_washed' => $request->has_washed, 
                        'has_ironed' => $request->has_ironed,  
                        'is_favorite' => $request->is_favorite, 
                        'is_scheduled' => 0, 
                        'created_at' => date('Y-m-d H:i:s'), 
                        'created_by' => $user_id, 
                        'updated_at' => null, 
                        'deleted_at' => null
                    ]);

                    if($res){
                        // History
                        Audit::createHistory('Create', $request->clothes_name, $user_id);
                        $user = UserModel::getSocial($user_id);

                        $options = new DompdfOptions();
                        $options->set('defaultFont', 'Helvetica');
                        $dompdf = new Dompdf($options);
                        $datetime = now();
                        $header_template = Generator::generateDocTemplate('header');
                        $style_template = Generator::generateDocTemplate('style');
                        $footer_template = Generator::generateDocTemplate('footer');
                        $imageOnTableDoc = "";
                        if($clothes_image){
                            $imageOnTableDoc = "
                            <tr>
                                <th>Image</th>
                                <td style='text-align:center'><img style='margin:10px; width:500px;' src='$clothes_image'></td>
                            </tr>";
                        }
                        $html = "
                            <html>
                                <head>
                                    $style_template
                                </head>
                                <body>
                                    $header_template
                                    <h3 style='margin:0 0 6px 0;'>clothes : {$request->clothes_name}</h3>
                                    <p style='margin:0; font-size:14px;'>ID : $id</p>
                                    <p style='margin-top:0; font-size:14px;'>Category : {$request->clothes_category} | Type : {$request->clothes_type}</p><br>
                                    <p style='font-size:13px; text-align: justify;'>
                                        At $datetime, this document has been generated from the new clothes called <b>{$request->clothes_name}</b>. You can also import this document into Wardrobe Apps or send it to our Telegram Bot if you wish to analyze the clothes. Important to know, that
                                        this document is <b>accessible for everyone</b> by using this link. Here you can see the item in this report:
                                    </p>                    
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Name</th>
                                                <td>{$request->clothes_name}</td>
                                            </tr>
                                            <tr>
                                                <th>Category</th>
                                                <td>{$request->clothes_category}</td>
                                            </tr>
                                            <tr>
                                                <th>Description</th>
                                                <td>" . ($request->clothes_desc ?? '-') . "</td>
                                            </tr>
                                            <tr>
                                                <th>Merk</th>
                                                <td>" . ($request->clothes_merk ?? '-') . "</td>
                                            </tr>
                                            <tr>
                                                <th>Color</th>
                                                <td>{$request->clothes_color}</td>
                                            </tr>
                                            <tr>
                                                <th>Price</th>
                                                <td>" . (isset($request->clothes_price) ? "Rp. " . number_format($request->clothes_price, 2, ',', '.') : '-') . "</td>
                                            </tr>
                                            <tr>
                                                <th>Image</th>
                                                <td><img src='{$clothes_image}' alt='Clothes Image' width='100'></td>
                                            </tr>
                                            <tr>
                                                <th>Size</th>
                                                <td>{$request->clothes_size}</td>
                                            </tr>
                                            <tr>
                                                <th>Gender</th>
                                                <td>{$request->clothes_gender}</td>
                                            </tr>
                                            <tr>
                                                <th>Made From</th>
                                                <td>{$request->clothes_made_from}</td>
                                            </tr>
                                            <tr>
                                                <th>Type</th>
                                                <td>{$request->clothes_type}</td>
                                            </tr>
                                            <tr>
                                                <th>Purchased At</th>
                                                <td>" . ($request->clothes_buy_at ?? '-') . "</td>
                                            </tr>
                                            <tr>
                                                <th>Quantity</th>
                                                <td>{$request->clothes_qty}</td>
                                            </tr>
                                            <tr>
                                                <th>Is Faded</th>
                                                <td>" . ($request->is_faded == 1 ? 'Yes' : 'No') . "</td>
                                            </tr>
                                            <tr>
                                                <th>Has Been Washed</th>
                                                <td>" . ($request->has_washed == 1 ? 'Yes' : 'No') . "</td>
                                            </tr>
                                            <tr>
                                                <th>Has Been Ironed</th>
                                                <td>" . ($request->has_ironed == 1 ? 'Yes' : 'No') . "</td>
                                            </tr>
                                            <tr>
                                                <th>Is Favorite</th>
                                                <td>" . ($request->is_favorite == 1 ? 'Yes' : 'No') . "</td>
                                            </tr>
                                            <tr>
                                                <th>Is Scheduled</th>
                                                <td>" . ($request->is_scheduled == 1 ? 'Yes' : 'No') . "</td>
                                            </tr>
                                            $imageOnTableDoc
                                        </tbody>
                                    </table>
                                    $footer_template
                                </body>
                            </html>";


                        $dompdf->loadHtml($html);
                        $dompdf->setPaper('A4', 'portrait');
                        $dompdf->render();

                        $message = "clothes created, its called '$request->clothes_name'";

                        if($user && $user->telegram_is_valid == 1 && $user->telegram_user_id){
                            $pdfContent = $dompdf->output();
                            $pdfFilePath = public_path("clothes-$id-$request->clothes_name.pdf");
                            file_put_contents($pdfFilePath, $pdfContent);
                            $inputFile = InputFile::create($pdfFilePath, $pdfFilePath);
                            
                            $response = Telegram::sendDocument([
                                'chat_id' => $user->telegram_user_id,
                                'document' => $inputFile,
                                'caption' => $message,
                                'parse_mode' => 'HTML'
                            ]);
                            unlink($pdfFilePath);
                        }

                        // Send email
                        $ctx = 'Create clothes';
                        dispatch(new ProcessMailer($ctx, $res, $user->username, $user->email));
                        
                        return response()->json([
                            'status' => 'success',
                            'message' => $message,
                            'data' => $res
                        ], Response::HTTP_CREATED);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => Generator::getMessageTemplate("unknown_error", null),
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("conflict", "clothes name"),
                    ], Response::HTTP_CONFLICT);
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
     *     path="/api/v1/clothes/recover/{id}",
     *     summary="Recover clothes by id",
     *     tags={"Clothes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="clothes ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="clothes recovered",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="clothes recovered")
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
     *         description="clothes failed to recovered",
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
    public function recover_clothes_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;
            $clothes = ClothesModel::select('clothes_name')->where('id',$id)->first();

            $rows = ClothesModel::where('id', $id)
                ->where('created_by', $user_id)
                ->update([
                    'deleted_at' => null,
            ]);

            if($rows > 0){
                // History
                Audit::createHistory('Recover', $clothes->clothes_name, $user_id);
                
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("recover", 'clothes'),
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'clothes'),
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
     *     path="/api/v1/clothes/destroy_schedule/{id}",
     *     summary="Permentally delete schedule by id",
     *     tags={"Clothes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="schedule ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="schedule permentally deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="schedule permentally deleted")
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
     *         description="schedule not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="schedule not found")
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
    public function hard_delete_schedule_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            $rows = ScheduleModel::where('id', $id)
                ->where('created_by', $user_id)
                ->delete();

            if($rows > 0){
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("permentally delete", 'schedule'),
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'schedule'),
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
     *     path="/api/v1/clothes/generate/outfit",
     *     summary="Show clothes used history",
     *     tags={"Clothes"},
     *     @OA\Response(
     *         response=201,
     *         description="outfit generated",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="outfit generated"),
     *             @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                          @OA\Property(property="clothes_name", type="string", example="Short Sleeves Oversized"),
     *                          @OA\Property(property="clothes_type", type="string", example="Hat"),
     *                          @OA\Property(property="clothes_category", type="string", example="head"),
     *                          @OA\Property(property="clothes_merk", type="string", example="Nike"),
     *                          @OA\Property(property="clothes_made_from", type="string", example="Cotton"),
     *                          @OA\Property(property="clothes_color", type="string", example="blue"),
     *                          @OA\Property(property="clothes_image", type="string", example="https://image.png"),
     *                          @OA\Property(property="last_used", type="string", example="2024-04-10 22:10:56"),
     *                          @OA\Property(property="total_used", type="integer", example="2"),
     *                      )
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
     *         description="outfit not found",
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
    public function post_generated_outfit(Request $request)
    {
        try{
            $user_id = $request->user()->id;
            $type = $request->clothes_type;

            $clothes = ClothesModel::selectRaw('clothes.id, clothes_name,clothes_category,clothes_type,clothes_merk,clothes_made_from,clothes_color,clothes_image,
                MAX(clothes_used.created_at) as last_used, CAST(SUM(CASE WHEN clothes_used.id IS NOT NULL THEN 1 ELSE 0 END) as UNSIGNED) as total_used')
                ->leftjoin('clothes_used','clothes_used.clothes_id','=','clothes.id');

            if(strpos($type, ',')){
                $dcts = explode(",", $type);
                foreach ($dcts as $dt) {
                    $clothes = $clothes->orwhere('clothes_type',$dt); 
                }
            } else {
                $clothes = $clothes->where('clothes_type',$type); 
            }

            $res = $clothes->where('has_washed',1)
                ->groupby('clothes_type')
                ->get();

            if(count($res) > 0){
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("generate", 'outfit'),
                    'data' => $res
                ], Response::HTTP_CREATED);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'outfit'),
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
     * @OA\POST(
     *     path="/api/v1/clothes/save/outfit",
     *     summary="Create outfit",
     *     tags={"Clothes"},
     *     security={{"bearerAuth":{}}},
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
    public function post_save_outfit(Request $request)
    {
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
                    $id = Generator::getUUID();
                    $outfit = OutfitModel::create([
                        'id' => $id, 
                        'outfit_name' => $dt['outfit_name'], 
                        'outfit_note' => null, 
                        'is_favorite' => 0, 
                        'is_auto' => 1, 
                        'created_at' => date('Y-m-d H:i:s'), 
                        'created_by' => $user_id, 
                        'updated_at' => null
                    ]);

                    if($outfit){
                        $success_outfit++;
                        $message_outfit .= ($idx+1).". ".$dt['outfit_name']."\n";

                        foreach ($dt['data'] as $clothes) {
                            $outfit_rel = OutfitRelModel::create([
                                'id' => Generator::getUUID(), 
                                'outfit_id' => $id, 
                                'clothes_id' => $clothes['id'], 
                                'created_at' => date('Y-m-d H:i:s'), 
                                'created_by' => $user_id, 
                            ]);
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
                    $user = UserModel::getSocial($user_id);
                    if($user->telegram_user_id){
                        $message = "Hello, $user->username. You have successfully add $success_outfit outfit. Here's the detail :\n\n$message_outfit";

                        $response = Telegram::sendMessage([
                            'chat_id' => $user->telegram_user_id,
                            'text' => $message,
                            'parse_mode' => 'HTML'
                        ]);
                    }
                }

                if($failed_rel_outfit == 0){
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("custom", "outfit created with $success_rel_outfit clothes attached"),
                    ], Response::HTTP_CREATED);
                } else if($failed_rel_outfit > 0 && $success_rel_outfit > 0){
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
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/clothes/outfit/history/by/{id}",
     *     summary="Permentally delete outfit history by id",
     *     tags={"Clothes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="outfit history ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="outfit history permentally deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="outfit history permentally deleted")
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
     *             @OA\Property(property="message", type="string", example="outfit history not found")
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
    public function hard_delete_used_outfit_by_id(Request $request, $id){
        try{
            $user_id = $request->user()->id;

            $rows = OutfitUsedModel::where('id', $id)
                ->where('created_by', $user_id)
                ->delete();

            if($rows > 0){
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("permentally delete", 'outfit history'),
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'outfit history'),
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
     * @OA\POST(
     *     path="/api/v1/clothes/outfit/history/save",
     *     summary="Add outfit used history",
     *     tags={"Clothes"},
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
    public function post_save_outfit_history(Request $request)
    {
        try{
            $user_id = $request->user()->id;
            $outfit_id = $request->outfit_id;
            $is_exist = OutfitModel::isExist($outfit_id, $user_id);
            if (!$is_exist) {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'outfit'),
                ], Response::HTTP_NOT_FOUND);
            } else {
                $res = OutfitUsedModel::create([
                    'id' => Generator::getUUID(),
                    'outfit_id' => $outfit_id, 
                    'created_at' => date("Y-m-d H:i:s"),
                    'created_by' => $user_id
                ]);

                if($res){
                    $list_clothes = OutfitRelModel::getClothes($outfit_id, $user_id);
                    $success_clothes = 0;
                    $failed_clothes = 0;
                    $message_clothes = "";

                    foreach ($list_clothes as $dt) {
                        $clothes_history = ClothesUsedModel::create([
                            'id' => Generator::getUUID(),
                            'clothes_id' => $dt->id, 
                            'clothes_note' => null, 
                            'used_context' => $request->used_context, 
                            'created_at' => date("Y-m-d H:i:s"),
                            'created_by' => $user_id
                        ]);

                        if($clothes_history){
                            $message_clothes .= "- $dt->clothes_name ($dt->clothes_type)\n";
                            $success_clothes++;
                        } else {
                            $failed_clothes++;
                        }
                    }

                    if($success_clothes > 0){
                        $user = UserModel::getSocial($user_id);
                        if($user->telegram_user_id){
                            $message = "Hello, $user->username. You have successfully add $success_clothes clothes to history of used. Here's the detail :\n\n$message_clothes";
    
                            $response = Telegram::sendMessage([
                                'chat_id' => $user->telegram_user_id,
                                'text' => $message,
                                'parse_mode' => 'HTML'
                            ]);
                        }
                    }
    
                    if($failed_clothes == 0){
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("custom", "outfit history created with $success_clothes clothes attached"),
                        ], Response::HTTP_CREATED);
                    } else if($failed_clothes > 0 && $success_clothes > 0){
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
     *     summary="Add Clothes to outfit",
     *     tags={"Clothes"},
     *     security={{"bearerAuth":{}}},
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
    public function post_save_clothes_outfit(Request $request){
        try{
            $user_id = $request->user()->id;
            $outfit_id = $request->outfit_id;
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
                    // Validator
                    $request->merge([
                        'clothes_id' => $dt->clothes_id,
                        'clothes_name' => $dt->clothes_name,
                        'clothes_type' => $dt->clothes_type
                    ]);
                    $validator = Validation::getValidateClothes($request,'create_outfit_relation');
                    if ($validator->fails()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => "At the $idx-th clothes : ".$validator->errors()
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
                    } else {
                        $clothes_id = $dt->clothes_id;
                        $is_exist_clothes = OutfitRelModel::isExistClothes($user_id,$clothes_id,$outfit_id);

                        if(!$is_exist_clothes){
                            $outfit_rel = OutfitRelModel::create([
                                'id' => Generator::getUUID(),
                                'outfit_id' => $outfit_id, 
                                'clothes_id' => $clothes_id, 
                                'created_at' => date("Y-m-d H:i:s"),
                                'created_by' => $user_id
                            ]);

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
    
                if($failed_clothes == 0){
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("custom", "$success_clothes clothes attached"),
                    ], Response::HTTP_CREATED);
                } else if($failed_clothes > 0 && $success_clothes > 0){
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
     *     summary="Permentally remove clothes by id",
     *     tags={"Clothes"},
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
    public function hard_delete_clothes_outfit_by_id(Request $request, $clothes_id, $outfit_id){
        try{
            $user_id = $request->user()->id;

            $rows = OutfitRelModel::deleteRelation($user_id,$clothes_id,$outfit_id);

            if($rows > 0){
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("remove", 'clothes'),
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'clothes'),
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
     * @OA\POST(
     *     path="/api/v1/clothes/wash",
     *     summary="Create clothes wash history",
     *     tags={"Clothes"},
     *     security={{"bearerAuth":{}}},
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
    public function post_wash_clothes(Request $request){
        try{
            $user_id = $request->user()->id;

            $validator = Validation::getValidateWash($request,'create');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $clothes_id = $request->clothes_id;
                $cl = ClothesModel::find($clothes_id);

                if($cl){
                    $is_exist = WashModel::getActiveWash($clothes_id,$user_id);

                    if($is_exist){
                        return response()->json([
                            'status' => 'failed',
                            'message' => Generator::getMessageTemplate("custom", "clothes is still at wash"),
                        ], Response::HTTP_CONFLICT);
                    } else {
                        $res = WashModel::create([
                            'id' => Generator::getUUID(), 
                            'wash_note' => $request->wash_note, 
                            'clothes_id' => $clothes_id, 
                            'wash_type' => $request->wash_type, 
                            'wash_checkpoint' => $request->wash_checkpoint, 
                            'created_at' => date('Y-m-d H:i:s'), 
                            'created_by' => $user_id, 
                            'finished_at' => null
                        ]); 
    
                        if($res){
                            // History
                            Audit::createHistory('Wash', $cl->clothes_name, $user_id);
    
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
                        'message' => Generator::getMessageTemplate("not_found", 'clothes'),
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
}
