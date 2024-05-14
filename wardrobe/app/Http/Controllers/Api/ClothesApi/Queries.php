<?php

namespace App\Http\Controllers\Api\ClothesApi;
use App\Http\Controllers\Controller;

// Models
use App\Models\ClothesModel;
use App\Models\ClothesUsedModel;

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
}
