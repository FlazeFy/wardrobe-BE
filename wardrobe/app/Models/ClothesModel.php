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
    protected $fillable = ['id', 'clothes_name', 'clothes_desc', 'clothes_merk', 'clothes_size', 'clothes_gender', 'clothes_made_from', 'clothes_color', 'clothes_category', 'clothes_type', 'clothes_price', 'clothes_buy_at', 'clothes_qty', 'is_faded', 'has_washed', 'has_ironed', 'is_favorite', 'is_scheduled', 'created_at', 'created_by', 'updated_at', 'deleted_at'];

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
}
