<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutfitModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'outfit';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'outfit_name', 'outfit_note', 'is_favorite', 'is_auto', 'created_at', 'created_by', 'updated_at'];

    public static function getOneOutfit($type,$outfit_id,$user_id){
        $res = OutfitModel::selectRaw('outfit.id, outfit_name, is_favorite, CAST(SUM(CASE WHEN outfit_used.id IS NOT NULL THEN 1 ELSE 0 END) as UNSIGNED) as total_used, outfit_used.created_at as last_used')
            ->leftjoin('outfit_used','outfit_used.outfit_id','=','outfit.id')
            ->where('outfit.created_by',$user_id);
        
        if($type == 'direct'){
            $res = $res->where('outfit.id',$outfit_id)->first();
        } else if($type == 'last'){
            $res = $res->orderby('outfit_used.created_at','desc')->first();
        }

        if ($res && $res->id !== null) {
            return $res;
        } else {
            return null;
        }
    }

    public static function isExist($id, $user_id){
        $res = OutfitModel::where('id',$id)
            ->where('created_by',$user_id)
            ->first();
        
        return $res ? true : false;
    }

    public static function getAllOutfit($limit,$user_id){
        $res = OutfitModel::selectRaw('outfit.id, outfit_name, outfit_note, is_favorite, CAST(SUM(CASE WHEN outfit_used.id IS NOT NULL THEN 1 ELSE 0 END) as UNSIGNED) as total_used')
            ->leftjoin('outfit_used','outfit_used.outfit_id','=','outfit.id')
            ->orderby('total_used','desc')
            ->orderby('outfit.created_at','desc')
            ->groupby('outfit.id')
            ->where('outfit.created_by',$user_id)
            ->paginate($limit);

        return $res;
    }
}
