<?php

namespace App\Http\Controllers\Api\AuthApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

// Models
use App\Models\UserModel;
use App\Models\AdminModel;
use App\Models\UserRequestModel;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Validation;

// Jobs
use App\Jobs\WelcomeMailer;

/**
 * @OA\Info(
 *     title="Wardrobe API",
 *     version="1.0.0",
 *     description="API Documentation for Wardrobe BE",
 *     @OA\Contact(
 *         email="flazen.edu@gmail.com"
 *     )
 * )
*/

class Commands extends Controller
{
    /**
     * @OA\POST(
     *     path="/api/v1/login",
     *     summary="Sign in to the Apps",
     *     tags={"Auth"},
     *     @OA\Response(
     *         response=200,
     *         description="login successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="token", type="string", example="286|L5fqrLCDDCzPRLKngtm2FM9wq1IU2xFZSVAm10yp874a1a85"),
     *             @OA\Property(property="role", type="integer", example=1),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="id", type="string", example="83ce75db-4016-d87c-2c3c-db1e222d0001"),
     *                 @OA\Property(property="username", type="string", example="flazefy"),
     *                 @OA\Property(property="email", type="string", example="flazen.edu@gmail.com"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-14 02:28:37"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-25 09:37:20"),
     *             ),
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
     *         description="account is not found or have wrong password",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="wrong username or password")
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
    public function login(Request $request)
    {
        try {
            $validator = Validation::getValidateLogin($request);

            if ($validator->fails()) {
                $errors = $validator->messages();

                return response()->json([
                    'status' => 'failed',
                    'result' => $errors,
                    'token' => null
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $user = AdminModel::where('username', $request->username)->first();
                $role = 1;
                if($user == null){
                    $user = UserModel::where('username', $request->username)->first();
                    $role = 0;
                }

                if (!$user || !Hash::check($request->password, $user->password)) {
                    //if (!$user || ($request->password != $user->password)) {
                    return response()->json([
                        'status' => 'failed',
                        'result' => Generator::getMessageTemplate("custom", 'wrong password or username'),
                        'token' => null,                
                    ], Response::HTTP_UNAUTHORIZED);
                } else {
                    $token = $user->createToken('login')->plainTextToken;
                    unset($user->password);

                    return response()->json([
                        'status' => 'success',
                        'result' => $user,
                        'token' => $token,  
                        'role' => $role                  
                    ], Response::HTTP_OK);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/register",
     *     summary="Register to the Apps",
     *     tags={"Auth"},
     *     @OA\Response(
     *         response=201,
     *         description="login successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="token", type="string", example="AAA123"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="id", type="string", example="83ce75db-4016-d87c-2c3c-db1e222d0001"),
     *                 @OA\Property(property="username", type="string", example="flazefy"),
     *                 @OA\Property(property="email", type="string", example="flazen.edu@gmail.com"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-14 02:28:37"),
     *             ),
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
     *         response=409,
     *         description="username or email already been used",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="username or email already been used")
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
    public function register(Request $request)
    {
        try {
            $validator = Validation::getValidateRegister($request);

            if ($validator->fails()) {
                $errors = $validator->messages();

                return response()->json([
                    'status' => 'failed',
                    'result' => $errors,
                    'token' => null
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $validPass = Validation::hasNumber($request->password);

                if($validPass){
                    $is_exist = UserModel::where('username', $request->username)->orwhere('email',$request->email)->first();

                    if ($is_exist) {
                        return response()->json([
                            'status' => 'failed',
                            'result' => Generator::getMessageTemplate("custom", 'username or email already been used'),
                        ], Response::HTTP_CONFLICT);
                    } else {
                        $user = UserModel::create([
                            'id' => Generator::getUUID(), 
                            'username' => $request->username, 
                            'password' => Hash::make($request->password), 
                            'email' => $request->email, 
                            'telegram_is_valid' => 0, 
                            'telegram_user_id' => null, 
                            'firebase_fcm_token' => $request->firebase_fcm_token ?? null, 
                            'created_at' => date('Y-m-d H:i:s'), 
                            'updated_at' => null
                        ]);

                        if($user){
                            $token = $user->createToken('login')->plainTextToken;
                            unset($user->password);
                            unset($user->telegram_is_valid);
                            unset($user->telegram_user_id);
                            unset($user->firebase_fcm_token);
                            unset($user->updated_at);

                            $token = Generator::getToken();
                            UserRequestModel::create([
                                'id' => Generator::getUUID(),
                                'request_token' => $token,
                                'request_context' => 'register',
                                'created_at' => date("Y-m-d H:i:s"),
                                'created_by' => $user->id,
                                'validated_at' => null
                            ]);

                            dispatch(new WelcomeMailer($user->username, $user->email, $token));

                            return response()->json([
                                'status' => 'success',
                                'result' => $user,             
                            ], Response::HTTP_CREATED);
                        } else {
                            return response()->json([
                                'status' => 'error',
                                'message' => Generator::getMessageTemplate("unknown_error", null)
                            ], Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'result' => [
                            "password" => [
                                "Password must contain number"
                            ]
                        ],
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
