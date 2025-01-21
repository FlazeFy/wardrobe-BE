<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'history';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'history_type', 'history_context', 'created_at', 'created_by'];

    public static function getAll($user_id){
        $res = HistoryModel::select('*')
            ->where('created_by',$user_id)
            ->orderby('created_at', 'DESC')
            ->paginate(14);

        return $res;
    }
}
