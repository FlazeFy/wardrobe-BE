<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\UserModel;

class UserTrackModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'user_track';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'track_lat', 'track_long', 'track_source', 'created_at', 'created_by'];

    public static function getUserReadyFetchWeather(){
        $users = UserModel::with('latestTrack')->get();

        $res = $users->map(function ($user) {
            $track = $user->latestTrack;
            if (!$track || !$track->track_lat || !$track->track_long) {
                return null;
            } 

            return [
                'track_lat' => $user->latestTrack->track_lat,
                'track_long' => $user->latestTrack->track_long,
                'created_at' => $user->latestTrack->created_at,
                'user_id' => $user->id,
                'username' => $user->username,
                'telegram_is_valid' => $user->telegram_is_valid,
                'telegram_user_id' => $user->telegram_user_id,
                'firebase_fcm_token' => $user->firebase_fcm_token
            ];
        })->filter();

        return $res;
    }

    public static function getOldLastTrack($days){
        $users = UserModel::with('latestTrack')->get();

        $res = $users->map(function ($user) {
            if (!$user->latestTrack) return null;

            return [
                'track_lat' => $user->latestTrack->track_lat,
                'track_long' => $user->latestTrack->track_long,
                'last_track' => $user->latestTrack->created_at,
                'user_id' => $user->id,
                'username' => $user->username,
                'telegram_is_valid' => $user->telegram_is_valid,
                'telegram_user_id' => $user->telegram_user_id,
                'firebase_fcm_token' => $user->firebase_fcm_token
            ];
        })->filter();

        return count($res) > 0 ? $res : null;
    }
}
