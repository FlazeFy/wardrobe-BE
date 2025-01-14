<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'schedule';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'clothes_id', 'day', 'is_remind', 'schedule_note', 'created_at', 'created_by'];

    public static function checkDayAvailability($day, $clothes_id, $user_id){
        $res = ScheduleModel::selectRaw('1')
            ->where('day',$day)
            ->where('clothes_id',$clothes_id)
            ->where('created_by',$user_id)
            ->first();

        return $res ? false : true;
    }
}
