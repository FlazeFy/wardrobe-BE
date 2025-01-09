<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class UserModel extends Authenticatable
{
    use HasFactory;
    //use HasUuids;
    use HasApiTokens;
    public $incrementing = false;

    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'username', 'password', 'email', 'telegram_is_valid', 'telegram_user_id', 'created_at', 'updated_at'];

    public static function getRandom($null){
        if($null == 0){
            $data = UserModel::inRandomOrder()->take(1)->first();
            $res = $data->id;
        } else {
            $res = null;
        }
        
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
}
