<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Helper
use App\Helpers\Generator;

/**
 * @OA\Schema(
 *     schema="OutfitUsed",
 *     type="object",
 *     required={"id", "outfit_id", "created_at", "created_by"},
 *
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary key for the outfit usage record"),
 *     @OA\Property(property="outfit_id", type="string", maxLength=36, description="ID of the outfit that was used"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the outfit usage was created"),
 *     @OA\Property(property="created_by", type="string", maxLength=36, description="ID of the user who created the outfit usage record")
 * )
 */

class OutfitUsedModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'outfit_used';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'outfit_id', 'created_at', 'created_by'];

    public static function getOutfitHistory($id,$user_id){
        return OutfitUsedModel::select("created_at","id")
            ->where('outfit_id',$id)
            ->where('created_by',$user_id)
            ->paginate(14);
    }

    public static function getLastUsed($user_id){
        return OutfitUsedModel::select("outfit_used.created_at as used_at","outfit_name")
            ->join('outfit','outfit.id','=','outfit_used.outfit_id')
            ->where('outfit_used.created_by',$user_id)
            ->orderby('outfit_used.created_at','desc')
            ->first();
    }

    public static function getOutfitMostUsed($year = null,$user_id = null,$limit = 7){
        $res = OutfitUsedModel::selectRaw("outfit_name as context, COUNT(1) as total")
            ->join('outfit','outfit.id','=','outfit_used.outfit_id');

        if($year){
            $res = $res->whereYear('outfit_used.created_at',$year);
        }
        if($user_id){
            $res = $res->where('outfit_used.created_by',$user_id);
        }

        return $res->groupby('outfit_id')
            ->orderby('total','desc')
            ->limit($limit)
            ->get();
    }

    public static function getMonthlyUsedOutfitByOutfitID($year, $outfit_id, $user_id = null){
        $res = OutfitUsedModel::selectRaw("COUNT(1) as total, MONTH(created_at) as context")
            ->whereRaw("YEAR(created_at) = ?", [$year]);

        if($outfit_id != "all"){
            $res = $res->where('outfit_id', $outfit_id);
        }
        if($user_id){
            $res = $res->where('created_by', $user_id);
        }
            
        return $res->groupByRaw("MONTH(created_at)")->get();
    }

    public static function createOutfitUsed($outfit_id, $user_id){
        $data['id'] = Generator::getUUID();
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $user_id;
        $data['outfit_id'] = $outfit_id;

        return OutfitUsedModel::create($data);
    }

    public static function hardDeleteOutfitUsedById($id, $user_id){
        return OutfitUsedModel::where('id',$id)->where('created_by',$user_id)->delete();
    }
}
