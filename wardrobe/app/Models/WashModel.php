<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Wash",
 *     type="object",
 *     required={"id","clothes_id","wash_type","wash_checkpoint","created_at","created_by"},
 *
 *     @OA\Property(property="id", type="string", format="uuid", description="Wash ID"),
 *     @OA\Property(property="clothes_id", type="string", maxLength=36, description="Clothes ID"),
 *     @OA\Property(property="wash_note", type="string", maxLength=75, nullable=true, description="Wash note"),
 *     @OA\Property(property="wash_type", type="string", maxLength=36, description="Wash type"),
 *     @OA\Property(property="wash_checkpoint", type="object", description="Wash checkpoint details"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Created timestamp"),
 *     @OA\Property(property="created_by", type="string", maxLength=36, description="User ID who created the wash"),
 *     @OA\Property(property="finished_at", type="string", format="date-time", nullable=true, description="Finished timestamp")
 * )
 */

class WashModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'wash';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'wash_note', 'clothes_id', 'wash_type', 'wash_checkpoint', 'created_at', 'created_by', 'finished_at'];
    protected $casts = [
        'wash_checkpoint' => 'array'
    ];

    public static function getWashHistory($clothes_id, $user_id){
        return WashModel::select('wash_note','wash_type','wash_checkpoint','created_at','finished_at')
            ->where('clothes_id',$clothes_id)
            ->where('created_by',$user_id)
            ->orderby('created_at','desc')
            ->get();
    }

    public static function getActiveWash($clothes_id, $user_id){
        return WashModel::select('wash_note','wash_type','wash_checkpoint')
            ->where('clothes_id',$clothes_id)
            ->where('created_by',$user_id)
            ->whereNull('finished_at')
            ->first();
    }

    public static function getWashCalendar($user_id, $year, $month = null, $date = null){
        $res = WashModel::selectRaw("clothes.id, clothes_name, clothes_category, clothes_type, clothes_image, wash.created_at")
            ->join('clothes', 'clothes.id', '=', 'wash.clothes_id')
            ->where('wash.created_by', $user_id);

        if($date != null){
            $res = $res->whereDate('wash.created_at', $date);
        } else {
            $res = $res->whereYear('wash.created_at', '=', $year);
        }
                
        if($month && $date == null){
            $res = $res->whereMonth('wash.created_at', '=', $month);
        }
            
        return $res->orderby('wash.created_at', 'asc')->get();
    }

    public static function getYearlyWash($user_id = null){
        $res = WashModel::selectRaw("COUNT(1) as total, DATE(created_at) as context")
            ->whereRaw("DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)");
            
        if($user_id){
            $res = $res->where('created_by',$user_id);
        }

        return $res->groupByRaw("DATE(created_at)")->get();
    }

    public static function getWashExport($user_id, $is_no_arr = true){
        $res = WashModel::select('wash.id','clothes_name', 'wash_type', 'wash_note', 'wash_checkpoint', 'clothes_merk', 'clothes_made_from', 'clothes_color', 'clothes_type', 'wash.created_at as wash_at', 'finished_at')
            ->join('clothes','clothes.id','=','wash.clothes_id')
            ->where('wash.created_by',$user_id)
            ->orderby('wash.created_at','desc')
            ->get();

        $final_res = [];

        if($is_no_arr){
            foreach ($res as $dt) {    
                $wash_checkpoint = $dt->wash_checkpoint;
                $dt['wash_checkpoint'] = implode(', ', array_column($wash_checkpoint, 'checkpoint_name'));
                $final_res[] = $dt; 
            }
        } else {
            $final_res = $res;
        }

        return collect($final_res);
    }

    public static function getUnfinishedWash($user_id, $page){
        $res = WashModel::select('clothes_name', 'wash_type', 'wash_checkpoint', 'clothes_type', 'wash.created_at as wash_at')
            ->join('clothes','clothes.id','=','wash.clothes_id')
            ->where('wash.created_by',$user_id)
            ->whereNull('finished_at')
            ->orderby('wash.created_at','desc');

        if($page == "all"){
            return $res->get();
        } else {
            return $res->paginate(14);
        }
    }

    public static function getLastWash($user_id){
        return WashModel::select('clothes_name', 'wash.created_at as wash_at')
            ->join('clothes','clothes.id','=','wash.clothes_id')
            ->where('wash.created_by',$user_id)
            ->whereNotNull('finished_at')
            ->orderby('wash.created_at','desc')
            ->first();
    }

    public static function getWashSummary($user_id){
        return WashModel::selectRaw('
                COUNT(1) as total_wash, 
                MAX(clothes_name) as most_wash, 
                AVG(TIMESTAMPDIFF(HOUR, wash.created_at, wash.finished_at)) as avg_wash_dur_per_clothes,
                (COUNT(1) / GREATEST(TIMESTAMPDIFF(WEEK, MIN(wash.created_at), MAX(wash.created_at)), 1)) as avg_wash_per_week
            ')
            ->join('clothes','clothes.id','=','wash.clothes_id')
            ->where('wash.created_by',$user_id)
            ->whereNotNull('finished_at')
            ->first();
    }

    public static function hardDeleteWashByClothesId($clothes_id){
        return WashModel::where('clothes_id',$clothes_id)->delete();
    }
}
