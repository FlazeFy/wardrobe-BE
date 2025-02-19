<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutfitUsedModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'outfit_used';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'outfit_id', 'created_at', 'created_by'];

    public static function getOutfitHistory($id,$user_id){
        $res = OutfitUsedModel::select("created_at","id")
            ->where('outfit_id',$id)
            ->where('created_by',$user_id)
            ->paginate(14);

        return $res;
    }

    public static function getOutfitMostUsed($year = null,$user_id,$limit = 7){
        $res = OutfitUsedModel::selectRaw("outfit_name as context, COUNT(1) as total")
            ->join('outfit','outfit.id','=','outfit_used.outfit_id')
            ->where('outfit_used.created_by',$user_id);

        if($year){
            $res = $res->whereYear('outfit_used.created_at',$year);
        }

        $res = $res->groupby('outfit_id')
            ->orderby('total','desc')
            ->limit($limit)
            ->get();

        return $res;
    }
}
