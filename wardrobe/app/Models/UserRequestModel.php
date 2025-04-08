<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRequestModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'user_request';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'request_token', 'request_context', 'created_at', 'created_by', 'validated_at'];

    public static function validateToken($username, $token, $context){
        $res = UserRequestModel::select('user_request.created_at','user_request.id')
            ->join('users','users.id','=','user_request.created_by')
            ->where('username',$username)
            ->where('request_token',$token)
            ->where('request_context',$context)
            ->whereNull('validated_at')
            ->first();
        
        return $res ? $res : null;
    }
}
