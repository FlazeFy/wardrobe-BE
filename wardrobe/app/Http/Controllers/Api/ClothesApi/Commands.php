<?php

namespace App\Http\Controllers\Api\ClothesApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Messaging\Notification;

// Models
use App\Models\ClothesModel;
use App\Models\ClothesUsedModel;
use App\Models\WashModel;

// Helpers
use App\Helpers\Generator;

class Commands extends Controller
{
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
                    'message' => Generator::getMessageTemplate("create", "clothes"),
                    'data' => $res
                ], Response::HTTP_CREATED);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => Generator::getMessageTemplate("unknown_error", null),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            $factory = (new Factory)->withServiceAccount(base_path('/firebase/...'));
            $user_id = $request->user()->id;
            $request->merge([
                'is_favorite' => $request->is_favorite == 'off' ? 0 : 1
            ]);

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
                                'message' => Generator::getMessageTemplate("unknown_error", null),
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
                    $res = clothesModel::create([
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
}
