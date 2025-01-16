<?php

namespace App\Http\Controllers\Api\ChatApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

// Models
use App\Models\ClothesModel;
use App\Models\UserModel;
use App\Models\ClothesUsedModel;
use App\Models\WashModel;
use App\Models\ScheduleModel;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Validation;
use App\Helpers\Audit;
use App\Helpers\Firebase;

class Commands extends Controller
{
    public function post_chat(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            // Message
            $message = strtolower($request->message);
            $res = "";
            $tokens = explode(" ",$message);

            // Query
            $select_query = "SELECT "; 
            $from_query = "";

            $tables = [
                "clothes" => ["clothes"],
                "outfit" => ["outfit"],
                "wash" => ["wash", "laundry"],
            ];
            $get_command = ["get", "show", "fetch", "present"];
            $all_command = ["all", "every"];
        
            if (!empty(array_intersect($get_command, $tokens))) {
                $tokens = array_diff($tokens, $get_command);
        
                foreach ($tables as $table => $keywords) {
                    if (!empty(array_intersect($keywords, $tokens))) {
                        $from_query = $table;
                        $select_query .= !empty(array_intersect($all_command, $tokens)) ? "*" : "";
                        break;
                    }
                }
            }
        
            // Execute query
            if (!empty($from_query)) {
                $res = DB::select("$select_query FROM $from_query WHERE created_by = ?", [$user_id]);
        
                if (count($res) > 0) {
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("generate", 'chat'),
                        'data' => $res,
                    ], Response::HTTP_CREATED);
                }
            }
        
            return response()->json([
                'status' => 'failed',
                'message' => Generator::getMessageTemplate("not_found", "chat"),
            ], Response::HTTP_NOT_FOUND);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
