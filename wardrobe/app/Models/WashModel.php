<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public static function getWashHistory($clothes_id,$user_id){
        $res = WashModel::select('wash_note','wash_type','wash_checkpoint','created_at','finished_at')
            ->where('clothes_id',$clothes_id)
            ->where('created_by',$user_id)
            ->orderby('created_at','desc')
            ->get();

        return $res;
    }

    public static function getActiveWash($clothes_id,$user_id){
        $res = WashModel::select('wash_note','wash_type','wash_checkpoint')
            ->where('clothes_id',$clothes_id)
            ->where('created_by',$user_id)
            ->whereNull('finished_at')
            ->first();

        return $res;
    }

    public static function getWashCalendar($user_id, $year, $month){
        $res = WashModel::selectRaw("clothes.id, clothes_name, clothes_category, clothes_type, clothes_image, wash.created_at")
            ->join('clothes', 'clothes.id', '=', 'wash.clothes_id')
            ->where('wash.created_by', $user_id)
            ->whereYear('wash.created_at', '=', $year)
            ->whereMonth('wash.created_at', '=', $month)
            ->orderby('wash.created_at', 'asc')
            ->get();

        return $res;
    }

    public static function getYearlyWash($user_id){
        $res = WashModel::selectRaw("COUNT(1) as total, DATE(created_at) as context")
            ->whereRaw("DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)")
            ->where('created_by',$user_id)
            ->groupByRaw("DATE(created_at)")
            ->get();

        return $res;
    }

    public static function getWashExport($user_id){
        $res = WashModel::select('clothes_name', 'wash_type', 'wash_note', 'wash_checkpoint', 'clothes_merk', 'clothes_made_from', 'clothes_color', 'clothes_type', 'wash.created_at as wash_at', 'finished_at')
            ->join('clothes','clothes.id','=','wash.clothes_id')
            ->where('wash.created_by',$user_id)
            ->orderby('wash.created_at','desc')
            ->get();

        $final_res = [];
        foreach ($res as $dt) {    
            $wash_checkpoint = $dt->wash_checkpoint;
            $dt['wash_checkpoint'] = implode(', ', array_column($wash_checkpoint, 'checkpoint_name'));
            $final_res[] = $dt; 
        }

        return collect($final_res);
    }
}
