<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Others Model
use App\Models\UserModel;

/**
 * @OA\Schema(
 *     schema="UserTrack",
 *     type="object",
 *     required={"id","track_lat","track_long","track_source","created_at","created_by"},
 *
 *     @OA\Property(property="id", type="string", format="uuid", description="User track ID"),
 *     @OA\Property(property="track_lat", type="string", maxLength=255, description="Latitude coordinate"),
 *     @OA\Property(property="track_long", type="string", maxLength=255, description="Longitude coordinate"),
 *     @OA\Property(property="track_source", type="string", maxLength=16, description="Source of tracking data"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Created timestamp"),
 *     @OA\Property(property="created_by", type="string", maxLength=36, description="User ID who created the track")
 * )
 */

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
