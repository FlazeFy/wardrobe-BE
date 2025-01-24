<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class AdminModel extends Authenticatable
{
    use HasFactory;
    //use HasUuids;
    use HasApiTokens;
    public $incrementing = false;

    protected $table = 'admin';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'username', 'password', 'email', 'created_at', 'updated_at'];

    public static function getProfile($id){
        $res = AdminModel::select('username','email','created_at','updated_at')
            ->where('id',$id)
            ->first();

        return $res;
    }
}
