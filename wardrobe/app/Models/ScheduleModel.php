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

    public static function getScheduleByClothes($clothes_id, $user_id){
        $res = ScheduleModel::select('id','day','schedule_note','created_at','is_remind')
            ->where('clothes_id',$clothes_id)
            ->where('created_by',$user_id)
            ->get();

        return $res;
    }

    public static function getScheduleByDay($day, $user_id){
        $res = ScheduleModel::select('clothes.id','clothes_name','clothes_category','clothes_type','clothes_image','day')
            ->join('clothes','clothes.id','=','schedule.clothes_id');
        
        if($day != 'all'){
            $res = $res->where('day',$day);
        }

        $res = $res->where('schedule.created_by',$user_id);
        
        if($day == 'all'){
            $res = $res->orderByRaw("FIELD(day, 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun')");
        }

        return $res->get();
    }

    public static function getWeeklyScheduleCalendar($user_id, $date = null){
        $res = ScheduleModel::selectRaw("clothes.id, clothes_name, clothes_category, clothes_type, clothes_image, day")
            ->join('clothes', 'clothes.id', '=', 'schedule.clothes_id')
            ->where('schedule.created_by', $user_id);

        if ($date) {
            $dayOfWeek = date('D', strtotime($date)); 
            $res = $res->where('day', $dayOfWeek);
        }

        return $res->get();
    }
}
