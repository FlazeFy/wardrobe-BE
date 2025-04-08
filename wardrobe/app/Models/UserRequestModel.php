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
}
