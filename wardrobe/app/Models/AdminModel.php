<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

// Models
use App\Models\ClothesModel;
use App\Models\UserModel;
use App\Models\OutfitModel;
use App\Models\WashModel;
use App\Models\ErrorModel;
use App\Models\ClothesUsedModel;
use App\Models\QuestionModel;

/**
 * @OA\Schema(
 *     schema="Admin",
 *     type="object",
 *     required={"id", "username", "password", "email", "telegram_is_valid", "created_at"},
 *
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary key for the admin"),
 *     @OA\Property(property="username", type="string", maxLength=36, description="Username of the admin"),
 *     @OA\Property(property="password", type="string", maxLength=500, description="Hashed password of the admin"),
 *     @OA\Property(property="email", type="string", format="email", maxLength=500, description="Email address of the admin"),
 *     @OA\Property(property="telegram_user_id", type="string", maxLength=36, nullable=true, description="Telegram user ID associated with the admin"),
 *     @OA\Property(property="telegram_is_valid", type="boolean", description="Indicates whether the Telegram account has been verified"),
 *     @OA\Property(property="timezone", type="string", maxLength=9, nullable=true, description="Timezone used by the admin"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the admin was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true, description="Timestamp when the admin was last updated")
 * )
 */

class AdminModel extends Authenticatable
{
    use HasFactory;
    use HasApiTokens;
    public $incrementing = false;

    protected $table = 'admin';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'username', 'password', 'email', 'telegram_user_id', 'telegram_is_valid', 'created_at', 'updated_at'];

    public static function getProfile($id){
        return AdminModel::select('username','email','created_at','updated_at')->where('id',$id)->first();
    }

    public static function  getAllContact(){
        $res = AdminModel::select('id','username','email','telegram_user_id','telegram_is_valid')->get();

        return count($res) > 0 ? $res : null;
    }

    public static function getAppsSummaryForLastNDays($days){
        $res_clothes = ClothesModel::selectRaw('count(1) as total')
            ->whereDate('created_at', '>=', Carbon::now()->subDays($days))->first();

        $res_user = UserModel::selectRaw('count(1) as total')
            ->whereDate('created_at', '>=', Carbon::now()->subDays($days))->first();

        $res_outfit = OutfitModel::selectRaw('count(1) as total')
            ->whereDate('created_at', '>=', Carbon::now()->subDays($days))->first();

        $res_wash = WashModel::selectRaw('count(1) as total')
            ->whereDate('created_at', '>=', Carbon::now()->subDays($days))->first();

        $res_clothes_used = ClothesUsedModel::selectRaw('count(1) as total')
            ->whereDate('created_at', '>=', Carbon::now()->subDays($days))->first();

        $res_question = QuestionModel::selectRaw('count(1) as total')
            ->whereDate('created_at', '>=', Carbon::now()->subDays($days))->first();

        $res_error = ErrorModel::selectRaw('count(1) as total')
            ->whereDate('created_at', '>=', Carbon::now()->subDays($days))->first();

        $final_res = (object)[
            'clothes_created' => $res_clothes->total,
            'new_user' => $res_user->total,
            'outfit_generated' => $res_outfit->total,
            'wash_created' => $res_wash->total,
            'clothes_used' => $res_clothes_used->total,
            'question_created' => $res_question->total,
            'error_happen' => $res_error->total,
        ];

        return $final_res;
    }
}
