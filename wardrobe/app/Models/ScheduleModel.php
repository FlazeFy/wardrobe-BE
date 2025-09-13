<?php

namespace App\Models;

use Carbon\Carbon;
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

    public static function getPlanSchedule($day){
        $res = ScheduleModel::selectRaw('clothes_name,clothes_type,schedule_note,is_favorite,has_washed,username,telegram_user_id,telegram_is_valid,firebase_fcm_token')
            ->join('users','users.id','=','schedule.created_by')
            ->join('clothes','schedule.clothes_id','=','clothes.id')
            ->leftjoin('clothes_used','clothes_used.clothes_id','=','clothes.id')
            ->where('day', $day)
            ->where('is_remind', 1)
            ->groupBy('clothes.id')
            ->orderBy('username','asc')
            ->get();
            
        return count($res) > 0 ? $res : null;
    }

    public static function hardDeleteScheduleByClothesId($clothes_id){
        return ScheduleModel::where('clothes_id',$clothes_id)->delete();
    }
}
