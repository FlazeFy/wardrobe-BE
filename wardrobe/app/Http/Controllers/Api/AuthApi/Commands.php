<?php

namespace App\Http\Controllers\Api\AuthApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

// Models
use App\Models\UserModel;
use App\Models\AdminModel;
use App\Models\UserRequestModel;
// Helpers
use App\Helpers\Generator;
use App\Helpers\Validation;
// Jobs
use App\Jobs\WelcomeJob;

/**
 * @OA\Info(
 *     title="Wardrobe BE (API)",
 *     version="1.0.0",
 *     description="This document describes the Wardrobe BE (API), built with Laravel (PHP), MySQL as the primary database, and Firebase for cloud storage and NoSQL data storage.",
 *     @OA\Contact(
 *         email="flazen.edu@gmail.com"
 *     )
 * ),
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="JWT Authorization header using the Bearer scheme",
 * )
*/

class Commands extends Controller
{
    /**
     * @OA\POST(
     *     path="/api/v1/login",
     *     summary="Post Login (Basic Auth)",
     *     description="This authentication request is used to access the application and obtain an authorization token for accessing all protected APIs. This request interacts with the MySQL database.",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", example="flazefy"),
     *             @OA\Property(property="password", type="string", example="nopass123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="login successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="token", type="string", example="286|L5fqrLCDDCzPRLKngtm2FM9wq1IU2xFZSVAm10yp874a1a85"),
     *             @OA\Property(property="role", type="integer", example=1),
     *             @OA\Property(property="message", type="object",
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
    public function postLogin(Request $request){
        try {
            // Validate request body
            $validator = Validation::getValidateLogin($request);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $validator->messages()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                // Check for Admin account
                $user = AdminModel::getByUsername($request->username);
                $role = 1;
                if($user == null){
                    // Check for User account
                    $user = UserModel::getByUsername($request->username);
                    $role = 0;
                }

                // Verify username and password
                if (!$user || !Hash::check($request->password, $user->password)) {
                    return response()->json([
                        'status' => 'failed',
                        "message" => Generator::getMessageTemplate("custom", 'wrong password or username'),
                    ], Response::HTTP_UNAUTHORIZED);
                } else {
                    // Create Token
                    $token = $user->createToken('login')->plainTextToken;
                    unset($user->password);

                    // Return success response
                    return response()->json([
                        'status' => 'success',
                        'message' => $user,
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
     *     summary="Post Register Apps",
     *     description="This authentication request is used to register / sign up new account. This request interacts with the MySQL database and broadcast with mailer.",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password", "email"},
     *             @OA\Property(property="username", type="string", example="flazefy"),
     *             @OA\Property(property="password", type="string", example="nopass123"),
     *             @OA\Property(property="email", type="string", example="flazen.work@gmail.com"),
     *             @OA\Property(property="firebase_fcm_token", type="string", nullable=true, example="1a2b3c4d5e6f")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="login successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="token", type="string", example="AAA123"),
     *             @OA\Property(property="message", type="object",
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
    public function postRegister(Request $request){
        try {
            // Validate request body
            $validator = Validation::getValidateRegister($request);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    "message" => $validator->messages(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                // Make sure password has number
                $validPass = Validation::hasNumber($request->password);
                if($validPass){
                    // Make sure username or email not used
                    $is_exist = UserModel::isUsernameOrEmailUsed($request->username, $request->email);
                    if ($is_exist) {
                        return response()->json([
                            'status' => 'failed',
                            "message" => Generator::getMessageTemplate("custom", 'username or email already been used'),
                        ], Response::HTTP_CONFLICT);
                    } else {
                        // Create user
                        $user = UserModel::createUser([
                            'username' => $request->username, 
                            'password' => Hash::make($request->password), 
                            'email' => $request->email, 
                            'firebase_fcm_token' => $request->firebase_fcm_token ?? null
                        ]);
                        if($user){
                            $token = $user->createToken('login')->plainTextToken;
                            unset($user->password);
                            unset($user->telegram_is_valid);
                            unset($user->telegram_user_id);
                            unset($user->firebase_fcm_token);
                            unset($user->updated_at);

                            // Create user request
                            $token = Generator::getToken();
                            UserRequestModel::createUserRequest(['request_token' => $token, 'request_context' => 'register'], $user_id);

                            // Broadcast email
                            dispatch(new WelcomeJob($user->username, $user->email, $token));

                            // Return success response
                            return response()->json([
                                'status' => 'success',
                                "message" => $user,    
                                'message' => Generator::getMessageTemplate("custom", "account has been registered, check your email to get a token validation")      
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
                        "message" => [ "password" => [ "Password must contain number" ]],
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
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
     *     path="/api/v1/register/validate",
     *     summary="Post Validate Registered Account",
     *     description="This authentication request is used to validate request token for newly created account. This request interacts with the MySQL database.",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "token"},
     *             @OA\Property(property="username", type="string", example="flazefy"),
     *             @OA\Property(property="token", type="string", example="ABC123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="validate token successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="account has been validated. Welcome flazefy")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="{validation_msg} | token is expired",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="{field validation message} | token is expired")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="invalid token",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="invalid token")
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
    public function postValidateRegister(Request $request){
        try {
            // Validate request body
            $validator = Validation::getValidateRegisterToken($request);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    "message" => $validator->messages(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                // Make sure token is exist
                $is_exist = UserRequestModel::validateToken($request->username, $request->token, 'register');
                if ($is_exist == null) {
                    return response()->json([
                        'status' => 'failed',
                        "message" => Generator::getMessageTemplate("custom", 'invalid token'),
                    ], Response::HTTP_NOT_FOUND);
                } else {
                    // Check if token already expired
                    $date_request = $is_exist->created_at;
                    $date_now = Carbon::now();
                    $is_expired = $date_now->diffInMinutes($date_request) > 20;

                    if ($is_expired) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => Generator::getMessageTemplate("custom", 'the token has expired')
                        ], Response::HTTP_BAD_REQUEST);
                    } else {
                        // Update user request by ID
                        $res = UserRequestModel::updateUserRequestById(['validated_at' => date('Y-m-d H:i:s')], $is_exist->id);
                        if($res > 0){
                            // Return success response
                            return response()->json([
                                'status' => 'success',
                                'message' => Generator::getMessageTemplate("custom", "account has been validated. Welcome $request->username")      
                            ], Response::HTTP_OK);
                        } else {
                            return response()->json([
                                'status' => 'error',
                                'message' => Generator::getMessageTemplate("unknown_error", null)
                            ], Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    }
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
     *     path="/api/v1/logout",
     *     summary="Post Log Out",
     *     description="This authentication request is used to sign out from application or reset current session. This request interacts with the MySQL database.",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Logout success"),
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
    public function postLogout(Request $request){
        try {
            $user_id = $request->user()->id;
            
            // Reset session & token
            session()->flush();
            $request->user()->currentAccessToken()->delete();

            // Return success response
            return response()->json([
                'status' => 'success',
                'message' => Generator::getMessageTemplate("custom", 'logout success')
            ], Response::HTTP_OK);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
