<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

// Models
use App\Models\ClothesModel;
use App\Models\ClothesUsedModel;
use App\Models\OutfitModel;
use App\Models\OutfitRelModel;
use App\Models\OutfitUsedModel;
use App\Models\WashModel;
use App\Models\ScheduleModel;

class UserModel extends Authenticatable
{
    use HasFactory;
    //use HasUuids;
    use HasApiTokens;
    public $incrementing = false;

    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'username', 'password', 'email', 'telegram_is_valid', 'telegram_user_id', 'firebase_fcm_token', 'created_at', 'updated_at'];

    public static function getRandom($null){
        if($null == 0){
            $data = UserModel::inRandomOrder()->take(1)->first();
            $res = $data->id;
        } else {
            $res = null;
        }
        
        return $res;
    }

    public static function getSocial($id){
        $res = UserModel::select('username','telegram_user_id','telegram_is_valid','email','firebase_fcm_token')
            ->where('id',$id)
            ->first();

        return $res;
    }

    public static function getRandomWhoHaveClothes($null){
        if($null == 0){
            $res = UserModel::inRandomOrder()
                ->select('users.id')
                ->join('clothes', 'clothes.created_by', '=', 'users.id')
                ->whereNotNull('clothes.id')
                ->first();
            
            $res = $res->id;
        } else {
            $res = null;
        }
        
        return $res;
    }

    public static function getProfile($id){
        $res = UserModel::select('username','email','telegram_user_id','telegram_is_valid','created_at','updated_at')
            ->where('id',$id)
            ->first();

        return $res;
    }

    public static function getMyAvailableYearFilter($user_id){
        $clothes_years = ClothesModel::selectRaw('YEAR(created_at) as year')
            ->where('created_by', $user_id)
            ->groupby('year')
            ->get();

        $clothes_used_years = ClothesUsedModel::selectRaw('YEAR(created_at) as year')
            ->where('created_by', $user_id)
            ->groupby('year')
            ->get();

        $outfit_years = OutfitModel::selectRaw('YEAR(created_at) as year')
            ->where('created_by', $user_id)
            ->groupby('year')
            ->get();

        $outfit_rel_years = OutfitRelModel::selectRaw('YEAR(created_at) as year')
            ->where('created_by', $user_id)
            ->groupby('year')
            ->get();

        $outfit_used_years = OutfitUsedModel::selectRaw('YEAR(created_at) as year')
            ->where('created_by', $user_id)
            ->groupby('year')
            ->get();

        $wash_years = WashModel::selectRaw('YEAR(created_at) as year')
            ->where('created_by', $user_id)
            ->groupby('year')
            ->get();

        $schedule_years = ScheduleModel::selectRaw('YEAR(created_at) as year')
            ->where('created_by', $user_id)
            ->groupby('year')
            ->get();

        $additional_years = collect([['year' => date('Y')]]);

        $res = $clothes_years->concat($clothes_used_years)
            ->concat($outfit_years)
            ->concat($outfit_rel_years)
            ->concat($outfit_used_years)
            ->concat($wash_years)
            ->concat($schedule_years)
            ->concat($additional_years)
            ->unique('year') 
            ->sortBy('year')
            ->values(); 

        return $res;
    }

    public static function getUserReadyGeneratedOutfit(){
        $res = UserModel::selectRaw("
                users.id,username,telegram_is_valid,telegram_user_id,firebase_fcm_token,
                CAST(SUM(CASE WHEN clothes_category = 'upper_body' THEN 1 ELSE 0 END) AS UNSIGNED) AS total_upper_body,
                CAST(SUM(CASE WHEN clothes_category = 'bottom_body' THEN 1 ELSE 0 END) AS UNSIGNED) AS total_bottom_body,
                CAST(SUM(CASE WHEN clothes_category = 'foot' THEN 1 ELSE 0 END) AS UNSIGNED) AS total_foot
            ")
            ->join('clothes', 'clothes.created_by', '=', 'users.id')
            ->groupBy('users.id')
            ->get();

        return count($res) > 0 ? $res : null;
    }
}
