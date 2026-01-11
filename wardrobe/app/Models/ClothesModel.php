<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

// Helper
use App\Helpers\Generator;

/**
 * @OA\Schema(
 *     schema="Clothes",
 *     type="object",
 *     required={
 *         "id", "clothes_name", "clothes_size", "clothes_gender", "clothes_made_from", "clothes_color", "clothes_category", "clothes_type", "clothes_qty",
 *         "is_faded", "has_washed", "has_ironed", "is_favorite", "is_scheduled", "created_at", "created_by"
 *     },
 *
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary key for the clothes item"),
 *     @OA\Property(property="clothes_name", type="string", maxLength=36, description="Name of the clothes item"),
 *     @OA\Property(property="clothes_desc", type="string", maxLength=255, nullable=true, description="Description of the clothes item"),
 *     @OA\Property(property="clothes_merk", type="string", maxLength=75, nullable=true, description="Brand or merk of the clothes"),
 *     @OA\Property(property="clothes_size", type="string", maxLength=3, description="Size of the clothes"),
 *     @OA\Property(property="clothes_gender", type="string", maxLength=6, description="Gender category of the clothes"),
 *     @OA\Property(property="clothes_made_from", type="string", maxLength=36, description="Material used to make the clothes, referenced from dictionary"),
 *     @OA\Property(property="clothes_color", type="string", maxLength=36, description="Color of the clothes"),
 *     @OA\Property(property="clothes_category", type="string", maxLength=36, description="Category of the clothes"),
 *     @OA\Property(property="clothes_type", type="string", maxLength=36, description="Type of the clothes, referenced from dictionary"),
 *     @OA\Property(property="clothes_price", type="integer", nullable=true, description="Purchase price of the clothes"),
 *     @OA\Property(property="clothes_buy_at", type="string", format="date", nullable=true, description="Date when the clothes were purchased"),
 *     @OA\Property(property="clothes_qty", type="integer", description="Quantity of the clothes item"),
 *     @OA\Property(property="is_faded", type="boolean", description="Indicates whether the clothes color has faded"),
 *     @OA\Property(property="has_washed", type="boolean", description="Indicates whether the clothes have been washed"),
 *     @OA\Property(property="has_ironed", type="boolean", description="Indicates whether the clothes have been ironed"),
 *     @OA\Property(property="is_favorite", type="boolean", description="Indicates whether the clothes are marked as favorite"),
 *     @OA\Property(property="is_scheduled", type="boolean", description="Indicates whether the clothes are scheduled for use"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the clothes were created"),
 *     @OA\Property(property="created_by", type="string", maxLength=36, description="ID of the user who created the clothes"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true, description="Timestamp when the clothes were last updated"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Timestamp when the clothes were soft deleted")
 * )
 */

class ClothesModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'clothes';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'clothes_name', 'clothes_desc', 'clothes_merk', 'clothes_size', 'clothes_gender', 'clothes_made_from', 'clothes_color', 'clothes_category', 'clothes_type', 'clothes_price', 'clothes_buy_at', 'clothes_qty', 'clothes_image', 'is_faded', 'has_washed', 'has_ironed', 'is_favorite', 'is_scheduled', 'created_at', 'created_by', 'updated_at', 'deleted_at'];

    public static function getRandom($null, $user_id, $exception_type = null){
        if($null == 0){
            $query = ClothesModel::inRandomOrder()->where('created_by', $user_id);

            if (!empty($exception_type)) {
                $query->whereNotIn('clothes_type', $exception_type);
            }

            $data = $query->first();
            $res = $data ? $data->id : null;
        } else {
            $res = null;
        }
        
        return $res;
    }

    public static function getAllClothesHeader($page, $category, $order, $is_detail = false, $user_id){
        $res = ClothesModel::selectRaw($is_detail ? '*' : 'id, clothes_name, clothes_image, clothes_size, clothes_gender, clothes_color, clothes_category, clothes_type, clothes_qty, is_faded, has_washed, has_ironed, is_favorite, is_scheduled');
            
        if($category != "all"){
            $res = $res->where('clothes_category',$category);
        }
        
        $res = $res->where('created_by',$user_id)
            ->whereNull('deleted_at')
            ->orderBy('is_favorite', 'desc')
            ->orderBy('clothes_name', $order)
            ->orderBy('created_at', $order);

        if($page != "all"){
            return $res->paginate($page ?? 14);
        } else {
            return $res->get();
        }
    }

    public static function getClothesSimiliarBy($ctx, $val, $user_id, $exc){
        return ClothesModel::select('id', 'clothes_name', 'clothes_image', 'clothes_category', 'clothes_type')
            ->where($ctx, 'like', "%$val%")                
            ->where('created_by',$user_id)
            ->whereNot('id',$exc)
            ->orderBy('is_favorite', 'desc')
            ->orderBy('clothes_name', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();
    }

    public static function getClothesById($id, $user_id){
        return ClothesModel::where('id',$id)->where('created_by',$user_id)->first();
    }

    public static function getRandomWithFreeSchedule($user_id){
        return ClothesModel::select(
                'clothes.id', DB::raw("GROUP_CONCAT(schedule.day ORDER BY schedule.day SEPARATOR ', ') as days")
            )
            ->leftJoin('schedule', 'schedule.clothes_id', '=', 'clothes.id')
            ->where('clothes.created_by', $user_id)
            ->groupBy('clothes.id', 'clothes_type')
            ->havingRaw('COUNT(schedule.id) < 7')
            ->inRandomOrder()
            ->first();
    }

    public static function getStatsSummary($user_id = null){
        $res = ClothesModel::selectRaw('COUNT(1) as total_clothes, MAX(clothes_price) as max_price, CAST(AVG(clothes_price) as UNSIGNED) as avg_price, CAST(SUM(clothes_qty) as UNSIGNED) as sum_clothes_qty');

        if($user_id){
            $res = $res->where('created_by',$user_id);
        }
            
        return $res->first();
    }

    public static function getDeletedClothes($user_id){
        return ClothesModel::select('id', 'clothes_name', 'clothes_image', 'clothes_size', 'clothes_gender', 'clothes_color', 'clothes_category', 'clothes_type', 'clothes_qty', 'deleted_at')
            ->whereNotNull('deleted_at')
            ->where('created_by',$user_id)
            ->orderBy('deleted_at', 'desc')
            ->paginate(14);
    }

    public static function getCategoryAndType($user_id){
        return ClothesModel::selectRaw('clothes_category,clothes_type,COUNT(1) as total')
            ->where('created_by',$user_id)
            ->groupby('clothes_category')
            ->groupby('clothes_type')
            ->get();
    }

    public static function getContextStats($ctx, $user_id){
        $rows = ClothesModel::selectRaw("REPLACE(CONCAT(UPPER(SUBSTRING($ctx, 1, 1)), LOWER(SUBSTRING($ctx, 2))), '_', ' ') as context, COUNT(1) as total");

        if($user_id){
            $rows = $rows->where('created_by', $user_id);
        } 

        return $rows->where($ctx,'!=','')
            ->whereNotNull($ctx)
            ->groupBy($ctx)
            ->orderBy('total', 'desc')
            ->limit(7)
            ->get();
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
        
        return $res->orderby('clothes_buy_at', 'asc')->get();
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

        return $res->orderby('created_at', 'asc')->get();
    }

    public static function getMonthlyClothesCreatedBuyed($user_id = null, $year, $col){
        $res = ClothesModel::selectRaw("COUNT(1) as total, MONTH($col) as context")
            ->whereYear($col, '=', $year);

        if($user_id){
            $res = $res->where('created_by', $user_id);
        }

        return $res->whereNotNull($col)->groupByRaw("MONTH($col)")->get();
    }

    public static function getMonthlyClothesUsed($user_id, $year){
        return ClothesModel::selectRaw("COUNT(1) as total, MONTH(clothes_used.created_at) as context")
            ->join('clothes_used','clothes_used.clothes_id','=','clothes.id')
            ->whereYear('clothes_used.created_at', '=', $year)
            ->where('clothes_used.created_by', $user_id)
            ->groupByRaw("MONTH(clothes_used.created_at)")
            ->get();
    }

    public static function getYearlyClothesCreatedBuyed($user_id = null, $target){
        $res = ClothesModel::selectRaw("COUNT(1) as total, DATE($target) as context")
            ->whereRaw("DATE($target) >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)");

        if($user_id){
            $res = $res->where('created_by', $user_id);
        }

        return $res->groupByRaw("DATE($target)")->get();
    }

    public static function getClothesExport($user_id, $type){
        $res = ClothesModel::select('*')
            ->where('created_by', $user_id);

        if($type == 'active'){
            $res = $res->whereNull('deleted_at');
        } else {
            $res = $res->whereNotNull('deleted_at');
        }
        
        return $res->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($dt, $type) {
                if($type == 'active'){
                    unset($dt->deleted_at);
                }
                unset($dt->created_by);
                return $dt;
            });
    }

    public static function getLast($ctx,$user_id){
        $res = ClothesModel::selectRaw("clothes_name, $ctx")
            ->where('created_by', $user_id);

        if($ctx == "deleted_at"){
            $res = $res->whereNotNull('deleted_at');
        }

        return $res->orderby("$ctx",'DESC')->first();
    }

    public static function getMostUsedClothesByDayAndType($user_id,$day){
        return ClothesModel::selectRaw('clothes.id,clothes_name,clothes_type,clothes_image,clothes_category,COUNT(1) as total,MAX(clothes.created_at) as last_used')
            ->join('clothes_used','clothes_used.clothes_id','=','clothes.id')
            ->where('clothes.created_by',$user_id)
            ->whereNull('deleted_at')
            ->whereRaw('LEFT(DAYNAME(clothes_used.created_at),3) = ?', [$day])
            ->groupBy('clothes_type')
            ->orderby('clothes_type','ASC')
            ->get();
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

        return collect($colorCounts)
            ->sortDesc()
            ->map(function ($count, $color) {
                return [
                    'context' => $color,
                    'total' => $count
                ];
            })
            ->values();
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

    public static function getClothesForOutfit($type = null, $user_id){
        $res = ClothesModel::selectRaw('clothes.id, clothes_name, clothes_category, clothes_type, clothes_merk, clothes_made_from, clothes_color, 
            clothes_image, MAX(clothes_used.created_at) as last_used, CAST(SUM(CASE WHEN clothes_used.id IS NOT NULL THEN 1 ELSE 0 END) as UNSIGNED) as total_used')
            ->leftJoin('clothes_used', 'clothes_used.clothes_id', '=', 'clothes.id')
            ->whereNotIn('clothes_type', ['swimsuit', 'underwear', 'tie', 'belt'])
            ->whereIn('clothes_category', ['upper_body', 'bottom_body', 'foot', 'head'])
            ->where('clothes.created_by', $user_id)
            ->where('has_washed', 1);

        if($type){
            if (strpos($type, ',')) {
                $types = explode(",", $type);
                $res->whereIn('clothes_type', $types);
            } else {
                $res->where('clothes_type', $type);
            }
        }
        
        return $res->groupBy('clothes.id')->get();
    }

    public static function createClothes($data, $user_id){
        $data['id'] = Generator::getUUID();
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $user_id;
        $data['updated_at'] = null;
        $data['deleted_at'] = null;
        $data['is_scheduled'] = 0;

        return ClothesModel::create($data);
    }

    public static function isClothesNameUsed($clothes_name, $user_id){
        return ClothesModel::where('clothes_name',$clothes_name)->where('created_by', $user_id)->exists();
    }

    public static function updateClothesById($data, $id, $user_id){
        $keys = array_keys($data);
        if (!(count($keys) === 1 && $keys[0] === 'deleted_at')) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        return ClothesModel::where('id',$id)->where('created_by',$user_id)->update($data);
    }
}
