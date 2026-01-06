<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Helper
use App\Helpers\Generator;

/**
 * @OA\Schema(
 *     schema="OutfitRelation",
 *     type="object",
 *     required={"id", "outfit_id", "clothes_id", "created_at", "created_by"},
 *
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary key for the outfit–clothes relation"),
 *     @OA\Property(property="outfit_id", type="string", maxLength=36, description="ID of the related outfit"),
 *     @OA\Property(property="clothes_id", type="string", maxLength=36, description="ID of the related clothes item"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the outfit–clothes relation was created"),
 *     @OA\Property(property="created_by", type="string", maxLength=36, description="ID of the user who created the relation")
 * )
 */

class OutfitRelModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'outfit_relation';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'outfit_id', 'clothes_id', 'created_at', 'created_by'];

    public static function getClothes($id, $user_id){
        return OutfitRelModel::select('clothes_name','clothes.id','clothes_type')
            ->join('clothes','clothes.id','=','outfit_relation.clothes_id')
            ->where('outfit_id',$id)
            ->where('clothes.created_by',$user_id)
            ->get();
    }

    public static function getClothesFoundInOutfit($clothes_id, $user_id){
        return OutfitRelModel::selectRaw('outfit.id, outfit_name, outfit_note, is_favorite, outfit.created_at, MAX(outfit_used.created_at) as last_used, CAST(SUM(CASE WHEN outfit_used.id IS NOT NULL THEN 1 ELSE 0 END) as UNSIGNED) as total_used')
            ->join('outfit','outfit.id','=','outfit_relation.outfit_id')
            ->leftjoin('outfit_used','outfit.id','=','outfit_used.outfit_id')
            ->where('clothes_id',$clothes_id)
            ->where('outfit_relation.created_by',$user_id)
            ->groupby('outfit.id')
            ->orderby('outfit.created_at','desc')
            ->get();
    }

    public static function getClothesByOutfit($outfit_id, $type){
        if($type == "full"){
            $select_query = "clothes.id,clothes_name,clothes_type,clothes_merk,clothes_image,has_washed,clothes_color";
        } else if($type == "header"){
            $select_query = "clothes_name,clothes_type,clothes_image";
        }

        return OutfitRelModel::selectRaw($select_query)
            ->join('clothes', 'clothes.id', '=', 'outfit_relation.clothes_id')
            ->where('outfit_id', $outfit_id)
            ->get();
    }

    public static function createOutfitRel($data, $user_id){
        $data['id'] = Generator::getUUID();
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $user_id;

        return OutfitRelModel::create($data);
    }

    public static function deleteRelation($user_id, $clothes_id, $outfit_id){
        return OutfitRelModel::where('clothes_id', $clothes_id)
            ->where('created_by',$user_id)
            ->where('outfit_id',$outfit_id)
            ->delete();
    }

    public static function isExistClothes($user_id, $clothes_id, $outfit_id){
        return OutfitRelModel::where('clothes_id',$clothes_id)
            ->where('outfit_id', $outfit_id)
            ->where('created_by', $user_id)
            ->exists();
    }

    public static function hardDeleteOutfitRelByClothesId($clothes_id){
        return OutfitRelModel::where('clothes_id',$clothes_id)->delete();
    }
}
