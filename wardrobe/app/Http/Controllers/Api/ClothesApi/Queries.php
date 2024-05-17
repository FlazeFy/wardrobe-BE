<?php

namespace App\Http\Controllers\Api\ClothesApi;
use App\Http\Controllers\Controller;

// Models
use App\Models\ClothesModel;
use App\Models\ClothesUsedModel;
use App\Models\WashModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Queries extends Controller
{
    public function get_all_clothes_header(Request $request, $category, $order)
    {
        try{
            $user_id = $request->user()->id;

            $res = ClothesModel::select('id', 'clothes_name', 'clothes_size', 'clothes_gender', 'clothes_color', 'clothes_category', 'clothes_type', 'clothes_qty', 'is_faded', 'has_washed', 'has_ironed', 'is_favorite', 'is_scheduled')
                ->where('clothes_category',$category)
                ->where('created_by',$user_id)
                ->orderBy('is_favorite', 'desc')
                ->orderBy('clothes_name', $order)
                ->orderBy('created_at', $order)
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'clothes fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'clothes failed to fetched',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. Please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_clothes_similiar_by(Request $request, $ctx, $val,$exc)
    {
        try{
            $user_id = $request->user()->id;

            $res = ClothesModel::select('id', 'clothes_name', 'clothes_category', 'clothes_type')
                ->where($ctx,$val)
                ->where('created_by',$user_id)
                ->whereNot('id',$exc)
                ->orderBy('is_favorite', 'desc')
                ->orderBy('clothes_name', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(12)
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'clothes fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'clothes failed to fetched',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error'.$e->getMessage(),
                'message' => 'something wrong. Please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_all_clothes_detail(Request $request, $category, $order)
    {
        try{
            $user_id = $request->user()->id;

            $res = ClothesModel::select('*')
                ->where('clothes_category',$category)
                ->where('created_by',$user_id)
                ->orderBy('is_favorite', 'desc')
                ->orderBy('clothes_name', $order)
                ->orderBy('created_at', $order)
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'clothes fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'clothes failed to fetched',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. Please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_clothes_used_history(Request $request, $clothes_id, $order)
    {
        try{
            $user_id = $request->user()->id;

            $res = ClothesUsedModel::select('clothes_name','clothes_note','used_context','clothes.created_at')
                ->join('clothes','clothes.id','=','clothes_used.clothes_id')
                ->where('clothes_id',$clothes_id)
                ->orderBy('clothes_used.created_at', $order)
                ->orderBy('clothes_name', $order)
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'clothes used fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'clothes used failed to fetched',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. Please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_clothes_wash_status_by_clothes_id(Request $request, $clothes_id)
    {
        try{
            $user_id = $request->user()->id;

            $exist = ClothesModel::selectRaw('1')
                ->where('id',$clothes_id)
                ->first();

            if($exist){
                $res = ClothesUsedModel::selectRaw('1')
                    ->where('clothes_id',$clothes_id)
                    ->first();
                
                if ($res) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'This clothes is washed right now',
                        'data' => true
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'This clothes is ready to use',
                        'data' => false
                    ], Response::HTTP_OK);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'This clothes is not exist',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. Please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
