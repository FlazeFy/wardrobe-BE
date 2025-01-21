<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClothesModel extends Model
{
    use HasFactory;

    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'clothes';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'clothes_name', 'clothes_desc', 'clothes_merk', 'clothes_size', 'clothes_gender', 'clothes_made_from', 'clothes_color', 'clothes_category', 'clothes_type', 'clothes_price', 'clothes_buy_at', 'clothes_qty', 'clothes_image', 'is_faded', 'has_washed', 'has_ironed', 'is_favorite', 'is_scheduled', 'created_at', 'created_by', 'updated_at', 'deleted_at'];

    public static function getRandom($null,$user_id){
        if($null == 0){
            $data = ClothesModel::inRandomOrder()->take(1)->where('created_by',$user_id)->first();
            $res = $data->id;
        } else {
            $res = null;
        }
        
        return $res;
    }

    public static function getStatsSummary($user_id){
        $res = ClothesModel::selectRaw('
            COUNT(1) as total_clothes, MAX(clothes_price) as max_price, CAST(AVG(clothes_price) as UNSIGNED) as avg_price, CAST(SUM(clothes_qty) as UNSIGNED) as sum_clothes_qty')
            ->where('created_by',$user_id)
            ->first();

        return $res;
    }

    public static function getDeletedClothes($user_id){
        $res = ClothesModel::select('id', 'clothes_name', 'clothes_image', 'clothes_size', 'clothes_gender', 'clothes_color', 'clothes_category', 'clothes_type', 'clothes_qty', 'deleted_at')
            ->whereNotNull('deleted_at')
            ->where('created_by',$user_id)
            ->orderBy('deleted_at', 'desc')
            ->paginate(14);

        return $res;
    }

    public static function getCategoryAndType($user_id){
        $res = ClothesModel::selectRaw('clothes_category,clothes_type,COUNT(1) as total')
            ->where('created_by',$user_id)
            ->groupby('clothes_category')
            ->groupby('clothes_type')
            ->get();

        return $res;
    }
}
