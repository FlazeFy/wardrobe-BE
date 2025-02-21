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

    public static function getClothesBuyedCalendar($user_id, $year, $month = null){
        $res = ClothesModel::selectRaw("clothes.id, clothes_name, clothes_category, clothes_type, clothes_image, clothes_buy_at as created_at")
            ->where('created_by', $user_id)
            ->whereNotNull('clothes_buy_at')
            ->whereYear('clothes_buy_at', '=', $year);
                
        if($month){
            $res->whereMonth('clothes_buy_at', '=', $month);
        }
        
        $res->orderby('clothes_buy_at', 'asc')
            ->get();

        return $res;
    }

    public static function getClothesCreatedCalendar($user_id, $year, $month = null){
        $res = ClothesModel::selectRaw("clothes.id, clothes_name, clothes_category, clothes_type, clothes_image, created_at")
            ->where('created_by', $user_id)
            ->whereYear('created_at', '=', $year);

        if($month){
            $res->whereMonth('created_at', '=', $month);
        }

        $res->orderby('created_at', 'asc')
            ->get();

        return $res;
    }

    public static function getMonthlyClothesCreatedBuyed($user_id, $year, $col){
        $res = ClothesModel::selectRaw("COUNT(1) as total, MONTH($col) as context")
            ->whereYear($col, '=', $year)
            ->where('created_by', $user_id)
            ->whereNotNull($col)
            ->groupByRaw("MONTH($col)")
            ->get();

        return $res;
    }

    public static function getYearlyClothesCreatedBuyed($user_id, $target){
        $res = ClothesModel::selectRaw("COUNT(1) as total, DATE($target) as context")
            ->whereRaw("DATE($target) >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)")
            ->where('created_by', $user_id)
            ->groupByRaw("DATE($target)")
            ->get();

        return $res;
    }

    public static function getClothesExport($user_id, $type){
        $res = ClothesModel::select('*')
            ->where('created_by', $user_id);

        if($type == 'active'){
            $res->whereNull('deleted_at');
        } else {
            $res->whereNotNull('deleted_at');
        }
        
        $res = $res->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($dt, $type) {
                if($type == 'active'){
                    unset($dt->deleted_at);
                }
                unset($dt->created_by);
                return $dt;
            });

        return $res;
    }
}
