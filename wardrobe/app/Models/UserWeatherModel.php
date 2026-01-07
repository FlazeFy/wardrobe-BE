<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Helper
use App\Helpers\Generator;

/**
 * @OA\Schema(
 *     schema="UserWeather",
 *     type="object",
 *     required={"id","weather_temp","weather_humid","weather_city","weather_condition","weather_hit_from","created_at","created_by"},
 *
 *     @OA\Property(property="id", type="string", format="uuid", description="User weather ID"),
 *     @OA\Property(property="weather_temp", type="number", format="double", description="Weather temperature"),
 *     @OA\Property(property="weather_humid", type="number", format="double", description="Weather humidity"),
 *     @OA\Property(property="weather_city", type="string", maxLength=75, description="Weather city"),
 *     @OA\Property(property="weather_condition", type="string", maxLength=16, description="Weather condition"),
 *     @OA\Property(property="weather_hit_from", type="string", maxLength=36, description="Weather data source"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Created timestamp"),
 *     @OA\Property(property="created_by", type="string", maxLength=36, description="User ID who created the weather data")
 * )
 */

class UserWeatherModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'user_weather';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'weather_temp', 'weather_humid', 'weather_city', 'weather_condition', 'weather_hit_from', 'created_at', 'created_by'];

    public static function createUserWeather($data, $user_id){
        $data['id'] = Generator::getUUID();
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $user_id;

        return UserWeatherModel::create($data);
    }
}
