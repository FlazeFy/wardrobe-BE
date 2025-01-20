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
}
