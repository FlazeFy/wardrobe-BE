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
}
