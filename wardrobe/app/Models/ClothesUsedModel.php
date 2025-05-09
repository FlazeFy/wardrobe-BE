<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public static function getUsedClothesReadyToWash($days){
        $latestUsage = ClothesUsedModel::selectRaw('clothes_id, MAX(created_at) as latest_used_at')
            ->groupBy('clothes_id');
    
        $res = ClothesUsedModel::selectRaw('clothes_name,clothes_type,clothes_made_from,clothes_used.used_context,is_faded,is_scheduled,clothes_used.created_at,
            username,telegram_is_valid,telegram_user_id,firebase_fcm_token')
            ->joinSub($latestUsage, 'latest_usage', function($join) {
                $join->on('clothes_used.clothes_id', '=', 'latest_usage.clothes_id')
                     ->on('clothes_used.created_at', '=', 'latest_usage.latest_used_at');
            })
            ->join('clothes', 'clothes.id', '=', 'clothes_used.clothes_id')
            ->join('users', 'users.id', '=', 'clothes_used.created_by')
            ->whereDate('clothes_used.created_at', '<', Carbon::now()->subDays($days))
            ->whereNotExists(function ($query) use ($days) {
                $query->select(DB::raw(1))
                    ->from('wash')
                    ->whereColumn('wash.clothes_id', 'clothes_used.clothes_id')
                    ->whereBetween('wash.finished_at', [
                        DB::raw('clothes_used.created_at'),
                        DB::raw('NOW()')
                    ]);
            })
            ->orderBy('username', 'asc')
            ->orderBy('clothes_used.created_at', 'desc')
            ->get();
    
        return count($res) > 0 ? $res : null;
    }    
    

    public static function hardDeleteClothesUsedByClothesId($clothes_id){
        $res = ClothesUsedModel::where('clothes_id',$clothes_id)->delete();

        return $res;
    }
}
