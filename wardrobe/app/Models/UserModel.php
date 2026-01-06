<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

// Others Model
use App\Models\ClothesModel;
use App\Models\ClothesUsedModel;
use App\Models\OutfitModel;
use App\Models\OutfitRelModel;
use App\Models\OutfitUsedModel;
use App\Models\WashModel;
use App\Models\ScheduleModel;
use App\Models\UserTrackModel;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     required={"id", "username", "password", "email", "telegram_is_valid", "created_at" },
 *
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary key for the user"),
 *     @OA\Property(property="username", type="string", maxLength=36, description="Username of the user"),
 *     @OA\Property(property="password", type="string", maxLength=500, description="Hashed user password"),
 *     @OA\Property(property="email", type="string", maxLength=500, format="email", description="User email address"),
 *     @OA\Property(property="telegram_user_id", type="string", maxLength=36, nullable=true, description="Telegram user ID linked to the account"),
 *     @OA\Property(property="telegram_is_valid", type="boolean", description="Indicates whether the Telegram account has been verified"),
 *     @OA\Property(property="firebase_fcm_token", type="string", maxLength=255, nullable=true, description="Firebase Cloud Messaging token for push notifications"),
 *     @OA\Property(property="timezone", type="string", maxLength=9, nullable=true, description="User timezone"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the user was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true, description="Timestamp when the user was last updated")
 * )
 */

class UserModel extends Authenticatable
{
    use HasFactory;
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

    public static function getRandomWithClothesOutfit($null){
        if($null == 0){
            $data = UserModel::select('users.id')
                ->join('clothes','users.id','=','clothes.created_by')
                ->join('outfit','users.id','=','outfit.created_by')
                ->inRandomOrder()
                ->take(1)
                ->first();
            $res = $data->id;
        } else {
            $res = null;
        }
        
        return $res;
    }

    public static function getSocial($id){
        return UserModel::select('username','telegram_user_id','telegram_is_valid','email','firebase_fcm_token')->where('id',$id)->first();
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

        return $clothes_years->concat($clothes_used_years)
            ->concat($outfit_years)
            ->concat($outfit_rel_years)
            ->concat($outfit_used_years)
            ->concat($wash_years)
            ->concat($schedule_years)
            ->concat($additional_years)
            ->unique('year') 
            ->sortBy('year')
            ->values(); 
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

    public static function getByUsername($username){
        return UserModel::where('username',$username)->first();
    }

    public static function updateUserById($data,$id){
        if (!(count($data) === 1 && array_key_exists('firebase_fcm_token', $data))) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        return UserModel::where('id',$id)->update($data);
    }

    public function latestTrack()
    {
        return $this->hasOne(UserTrackModel::class, 'created_by')->latestOfMany('created_at');
    }
}
