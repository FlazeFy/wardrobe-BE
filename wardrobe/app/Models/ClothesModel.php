<?php

namespace App\Models;

use Carbon\Carbon;
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

    public static function getClothesBuyedCalendar($user_id, $year, $month = null, $date = null){
        $res = ClothesModel::selectRaw("clothes.id, clothes_name, clothes_category, clothes_type, clothes_image, clothes_buy_at as created_at")
            ->where('created_by', $user_id)
            ->whereNotNull('clothes_buy_at');

        if($date != null){
            $res = $res->whereDate('clothes_buy_at', $date);
        } else {
            $res = $res->whereYear('clothes_buy_at', '=', $year);;
        }
                
        if($month && $date == null){
            $res = $res->whereMonth('clothes_buy_at', '=', $month);
        }
        
        $res = $res->orderby('clothes_buy_at', 'asc')
            ->get();

        return $res;
    }

    public static function getClothesCreatedCalendar($user_id, $year, $month = null, $date = null){
        $res = ClothesModel::selectRaw("clothes.id, clothes_name, clothes_category, clothes_type, clothes_image, created_at")
            ->where('created_by', $user_id);

        if($date != null){
            $res = $res->whereDate('created_at', $date);
        } else {
            $res = $res->whereYear('created_at', '=', $year);;
        }

        if($month && $date == null){
            $res = $res->whereMonth('created_at', '=', $month);
        }

        $res = $res->orderby('created_at', 'asc')
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

    public static function getMonthlyClothesUsed($user_id, $year){
        $res = ClothesModel::selectRaw("COUNT(1) as total, MONTH(clothes_used.created_at) as context")
            ->join('clothes_used','clothes_used.clothes_id','=','clothes.id')
            ->whereYear('clothes_used.created_at', '=', $year)
            ->where('clothes_used.created_by', $user_id)
            ->groupByRaw("MONTH(clothes_used.created_at)")
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
            $res = $res->whereNull('deleted_at');
        } else {
            $res = $res->whereNotNull('deleted_at');
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

    public static function getLast($ctx,$user_id){
        $res = ClothesModel::selectRaw("clothes_name, $ctx")
            ->where('created_by', $user_id);

        if($ctx == "deleted_at"){
            $res = $res->whereNotNull('deleted_at');
        }

        $res = $res->orderby("$ctx",'DESC')
            ->first();

        return $res;
    }

    public static function getMostUsedClothesByDayAndType($user_id,$day){
        $res = ClothesModel::selectRaw('clothes.id,clothes_name,clothes_type,clothes_image,clothes_category,COUNT(1) as total,MAX(clothes.created_at) as last_used')
            ->join('clothes_used','clothes_used.clothes_id','=','clothes.id')
            ->where('clothes.created_by',$user_id)
            ->whereNull('deleted_at')
            ->whereRaw('LEFT(DAYNAME(clothes_used.created_at),3) = ?', [$day])
            ->groupBy('clothes_type')
            ->orderby('clothes_type','ASC')
            ->get();

        return $res;
    }

    public static function getMostUsedColor($id = null){
        $res = ClothesModel::select('clothes_color');
        if($id){
            $res = $res->whereNot('id', $id);
        }
        $res = $res->pluck('clothes_color');

        $colorCounts = [];
        foreach ($res as $colorString) {
            $individualColors = array_map('trim', explode(',', $colorString));
            foreach ($individualColors as $color) {
                if (!isset($colorCounts[$color])) {
                    $colorCounts[$color] = 0;
                }
                $colorCounts[$color]++;
            }
        }

        $final_res = collect($colorCounts)
            ->sortDesc()
            ->map(function ($count, $color) {
                return [
                    'context' => $color,
                    'total' => $count
                ];
            })
            ->values();

        return $final_res;
    }

    public static function getClothesPlanDestroy($days){
        $res = ClothesModel::select('clothes.id','clothes_name','username','telegram_user_id','telegram_is_valid','firebase_fcm_token')
            ->join('users','users.id','=','clothes.created_by')
            ->whereDate('deleted_at', '<', Carbon::now()->subDays($days))
            ->orderby('username','asc')
            ->get();

        return count($res) > 0 ? $res : null;
    }

    public static function getClothesPrePlanDestroy($days){
        $res = ClothesModel::selectRaw('clothes_name, deleted_at, count(1) as total_outfit_attached, username, telegram_user_id, telegram_is_valid, firebase_fcm_token')
            ->join('users','users.id','=','clothes.created_by')
            ->leftjoin('outfit_relation','outfit_relation.clothes_id','=','clothes.id')
            ->whereDate('deleted_at', Carbon::now()->subDays($days))
            ->groupby('clothes.id')
            ->orderby('username','asc')
            ->get();

        return count($res) > 0 ? $res : null;
    }

    public static function getUnwashedClothes(){
        $res = ClothesModel::select('clothes_name','clothes_buy_at','is_favorite','is_scheduled','username','telegram_user_id','telegram_is_valid','firebase_fcm_token')
            ->join('users','users.id','=','clothes.created_by')
            ->where('has_washed', 0)
            ->orderby('username','asc')
            ->get();

        return count($res) > 0 ? $res : null;
    }

    public static function getUnironedClothes(){
        $ironable_clothes_made_from = ['cotton','linen','silk','rayon'];
        $ironable_clothes_type = ['pants','shirt','jacket','shorts','skirt','dress','blouse','sweater','hoodie','tie','coat','vest','t-shirt','jeans','leggings','cardigan'];

        $res = ClothesModel::select('clothes_name','clothes_made_from','has_washed','is_favorite','is_scheduled','username','telegram_user_id','telegram_is_valid','firebase_fcm_token')
            ->join('users','users.id','=','clothes.created_by')
            ->where('has_ironed', 0)
            ->whereIn('clothes_made_from', $ironable_clothes_made_from)
            ->whereIn('clothes_type', $ironable_clothes_type)
            ->orderby('username','asc')
            ->get();

        return count($res) > 0 ? $res : null;
    }

    public static function getUnusedClothes($days){
        $res = ClothesModel::selectRaw('clothes_name,clothes_type,
            CASE 
                WHEN clothes_used.created_at IS NOT NULL THEN MAX(clothes_used.created_at) 
                ELSE clothes.created_at 
            END AS last_used,
            COUNT(clothes_used.id) as total_used,
            username,telegram_user_id,telegram_is_valid,firebase_fcm_token')
            ->join('users','users.id','=','clothes.created_by')
            ->leftjoin('clothes_used','clothes.id','=','clothes_used.clothes_id')
            ->groupby('clothes.id')
            ->orderby('username','asc')
            ->havingRaw('last_used < ?', [Carbon::now()->subDays($days)])
            ->get();

        return count($res) > 0 ? $res : null;
    }
}
