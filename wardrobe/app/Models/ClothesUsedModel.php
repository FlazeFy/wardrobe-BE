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
}
