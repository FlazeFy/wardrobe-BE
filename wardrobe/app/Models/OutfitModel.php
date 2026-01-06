<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Helper
use App\Helpers\Generator;

/**
 * @OA\Schema(
 *     schema="Outfit",
 *     type="object",
 *     required={"id", "outfit_name", "is_auto", "is_favorite", "created_at", "created_by"},
 *
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary key for the outfit"),
 *     @OA\Property(property="outfit_name", type="string", maxLength=36, description="Name of the outfit"),
 *     @OA\Property(property="outfit_note", type="string", maxLength=255, nullable=true, description="Additional note or description for the outfit"),
 *     @OA\Property(property="is_auto", type="boolean", description="Indicates whether the outfit is automatically generated"),
 *     @OA\Property(property="is_favorite", type="boolean", description="Indicates whether the outfit is marked as favorite"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the outfit was created"),
 *     @OA\Property(property="created_by", type="string", maxLength=36, description="ID of the user who created the outfit"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true, description="Timestamp when the outfit was last updated")
 * )
 */

class OutfitModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'outfit';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'outfit_name', 'outfit_note', 'is_favorite', 'is_auto', 'created_at', 'created_by', 'updated_at'];

    public static function getOneOutfit($type, $outfit_id, $user_id){
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

    public static function getOutfitById($id, $user_id){
        return OutfitModel::where('id',$id)->where('created_by',$user_id)->first();
    }

    public static function getAllOutfit($limit, $user_id){
        return OutfitModel::selectRaw('outfit.id, outfit_name, outfit_note, is_favorite, CAST(SUM(CASE WHEN outfit_used.id IS NOT NULL THEN 1 ELSE 0 END) as UNSIGNED) as total_used')
            ->leftjoin('outfit_used','outfit_used.outfit_id','=','outfit.id')
            ->orderby('total_used','desc')
            ->orderby('outfit.created_at','desc')
            ->groupby('outfit.id')
            ->where('outfit.created_by',$user_id)
            ->paginate($limit);
    }

    public static function getRandom($null,$user_id){
        if($null == 0){
            $data = OutfitModel::inRandomOrder()->take(1)->where('created_by',$user_id)->first();
            $res = $data->id;
        } else {
            $res = null;
        }
        
        return $res;
    }

    public static function createOutfit($data, $user_id){
        $data['id'] = Generator::getUUID();
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $user_id;
        $data['updated_at'] = null;

        return OutfitModel::create($data);
    }

    public static function isExist($id, $user_id){
        return OutfitModel::where('id',$id)->where('created_by',$user_id)->exists();
    }
}
