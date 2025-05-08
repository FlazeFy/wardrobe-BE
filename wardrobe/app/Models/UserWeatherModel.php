<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWeatherModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'user_weather';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'weather_temp', 'weather_humid', 'weather_city', 'weather_condition', 'weather_hit_from', 'created_at', 'created_by'];
}
