<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClothesUsedModel extends Model
{
    use HasFactory;

    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'clothes_used';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'clothes_id', 'clothes_note', 'used_context', 'created_at', 'created_by'];

    public static function getClothesUsedHistory($clothes_id,$user_id){
        $res = ClothesUsedModel::select('id','clothes_note','used_context','created_at')
            ->where('clothes_id',$clothes_id)
            ->where('created_by',$user_id)
            ->get();

        return $res;
    }

    public static function getLastUsed($user_id){
        $res = ClothesUsedModel::select('created_at')
            ->where('created_by',$user_id)
            ->orderby('created_at','ASC')
            ->first();

        return $res;
    }

    public static function getClothesUsedHistoryCalendar($user_id, $year, $month = null, $date = null){
        $res = ClothesUsedModel::selectRaw("clothes.id, clothes_name, clothes_category, clothes_type, clothes_image, clothes_used.created_at")
            ->join('clothes', 'clothes_used.clothes_id', '=', 'clothes.id')
            ->where('clothes_used.created_by', $user_id);

        if($date != null){
            $res = $res->whereDate('clothes_used.created_at', $date);
        } else {
            $res = $res->whereYear('clothes_used.created_at', '=', $year);
        }
        
        if($month && $date == null){
            $res = $res->whereMonth('clothes_used.created_at', '=', $month);
        }
        
        $res = $res->orderby('clothes_used.created_at', 'asc')
            ->get();

        return $res;
    }

    public static function getYearlyClothesUsed($user_id){
        $res = ClothesUsedModel::selectRaw("COUNT(1) as total, DATE(created_at) as context")
            ->whereRaw("DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)")
            ->where('created_by',$user_id)
            ->groupByRaw("DATE(created_at)")
            ->get();

        return $res;
    }

    public static function getClothesUsedExport($user_id){
        $res = ClothesUsedModel::select('clothes_name', 'clothes_note', 'used_context', 'clothes_merk', 'clothes_made_from', 'clothes_color', 'clothes_type', 'is_favorite', 'clothes_used.created_at as used_at')
            ->join('clothes','clothes.id','=','clothes_used.clothes_id')
            ->where('clothes_used.created_by',$user_id)
            ->orderby('clothes_used.created_at', 'desc')
            ->get();

        return $res;
    }
}
