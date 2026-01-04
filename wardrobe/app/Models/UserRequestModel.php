<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="UserRequest",
 *     type="object",
 *     required={"id","request_type","is_show","created_at","created_by"},
 *
 *     @OA\Property(property="id", type="string", format="uuid", description="User request ID"),
 *     @OA\Property(property="request_type", type="string", maxLength=144, description="Request type"),
 *     @OA\Property(property="request_context", type="string", maxLength=255, nullable=true, description="Request context"),
 *     @OA\Property(property="is_show", type="boolean", description="Request visibility status"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Created timestamp"),
 *     @OA\Property(property="created_by", type="string", maxLength=36, description="User ID who created the request")
 * )
 */

class UserRequestModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'user_request';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'request_token', 'request_context', 'created_at', 'created_by', 'validated_at'];

    public static function validateToken($username, $token, $context){  
        return UserRequestModel::select('user_request.created_at','user_request.id')
            ->join('users','users.id','=','user_request.created_by')
            ->where('username',$username)
            ->where('request_token',$token)
            ->where('request_context',$context)
            ->whereNull('validated_at')
            ->first();
    }
}
