<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutfitRelModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'outfit_relation';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'outfit_id', 'clothes_id', 'created_at', 'created_by'];

    public static function getClothes($id, $user_id){
        $res = OutfitRelModel::select('clothes_name','clothes.id','clothes_type')
            ->join('clothes','clothes.id','=','outfit_relation.clothes_id')
            ->where('outfit_id',$id)
            ->where('clothes.created_by',$user_id)
            ->get();
        
        return $res;
    }

    public static function getClothesFoundInOutfit($clothes_id,$user_id){
        $res = OutfitRelModel::selectRaw('outfit.id, outfit_name, outfit_note, is_favorite, outfit.created_at, MAX(outfit_used.created_at) as last_used, CAST(SUM(CASE WHEN outfit_used.id IS NOT NULL THEN 1 ELSE 0 END) as UNSIGNED) as total_used')
            ->join('outfit','outfit.id','=','outfit_relation.outfit_id')
            ->leftjoin('outfit_used','outfit.id','=','outfit_used.outfit_id')
            ->where('clothes_id',$clothes_id)
            ->where('outfit_relation.created_by',$user_id)
            ->groupby('outfit.id')
            ->orderby('outfit.created_at','desc')
            ->get();

        return $res;
    }

    public static function getClothesByOutfit($outfit_id, $type){
        if($type == "full"){
            $select_query = "clothes.id,clothes_name,clothes_type,clothes_merk,clothes_image,has_washed,clothes_color";
        } else if($type == "header"){
            $select_query = "clothes_name,clothes_type,clothes_image";
        }

        $res = OutfitRelModel::selectRaw($select_query)
            ->join('clothes', 'clothes.id', '=', 'outfit_relation.clothes_id')
            ->where('outfit_id', $outfit_id)
            ->get();

        return $res;
    }

    public static function deleteRelation($user_id,$clothes_id,$outfit_id){
        $res = OutfitRelModel::where('clothes_id', $clothes_id)
            ->where('created_by',$user_id)
            ->where('outfit_id',$outfit_id)
            ->delete();

        return $res;
    }

    public static function isExistClothes($user_id,$clothes_id,$outfit_id){
        $res = OutfitRelModel::where('clothes_id',$clothes_id)
            ->where('outfit_id', $outfit_id)
            ->where('created_by', $user_id)
            ->first();

        return $res ? true : false;
    }

    public static function hardDeleteOutfitRelByClothesId($clothes_id){
        return OutfitRelModel::where('clothes_id',$clothes_id)->delete();
    }
}
