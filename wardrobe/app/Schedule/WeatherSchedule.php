<?php

namespace App\Schedule;
use Carbon\Carbon;
use DateTime;
use Telegram\Bot\Laravel\Facades\Telegram;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use GuzzleHttp\Client;

// Model
use App\Models\UserTrackModel;
use App\Models\UserWeatherModel;
use App\Models\UserModel;
use App\Models\AdminModel;
// Helper
use App\Helpers\Generator;

class WeatherSchedule
{
    public static function weather_routine_fetch()
    {
        try{
            $user = UserTrackModel::getUserReadyFetchWeather();

            if($user){
                $client = new Client();
                $open_weather_key = env("OPEN_WEATHER_API_KEY");

                foreach ($user as $dt) {
                    $response = $client->get('https://api.openweathermap.org/data/2.5/weather', [
                        'query' => [
                            'lat' => $dt['track_lat'],
                            'lon' => $dt['track_long'],
                            'units' => 'metric',
                            'appid' => $open_weather_key
                        ]
                    ]);
                
                    $data = json_decode($response->getBody(), true);
                
                    $weather = (object)[
                        'temp' => $data['main']['temp'],
                        'humidity' => $data['main']['humidity'],
                        'city' => $data['name'],
                        'condition' => $data['weather'][0]['main']
                    ];
                
                    $res = UserWeatherModel::create([
                        'id' => Generator::getUUID(),
                        'weather_temp' => $weather->temp, 
                        'weather_humid' => $weather->humidity, 
                        'weather_city' => $weather->city, 
                        'weather_condition' => $weather->condition, 
                        'weather_hit_from' => 'Task Schedule', 
                        'created_at' => date("Y-m-d H:i:s"), 
                        'created_by' => $dt['user_id']
                    ]);
                
                    if ($res) {
                        $message = "Hello ".$dt['username'].", from your last coordinate ".$dt['track_lat'].", ".$dt['track_long']." at ".date("Y-m-d H:i",strtotime($dt['created_at'])).". We've have checked the weather for today, and the result is:\n\nTemperature: $weather->temp °C\nHumidity: $weather->humidity%\nCity: $weather->city\nWeather Condition: $weather->condition";
                
                        if($dt['telegram_user_id'] && $dt['telegram_is_valid'] == 1){
                            Telegram::sendMessage([
                                'chat_id' => $dt['telegram_user_id'],
                                'text' => $message,
                                'parse_mode' => 'HTML'
                            ]);
                        }

                        if($dt['firebase_fcm_token']){
                            $factory = (new Factory)->withServiceAccount(base_path('/firebase/wardrobe-26571-firebase-adminsdk-fint4-9966f0909b.json'));
                            $messaging = $factory->createMessaging();
                            $message_fcm = CloudMessage::withTarget('token', $dt['firebase_fcm_token'])
                                ->withNotification(Notification::create($message, $res->id));
                            $response = $messaging->send($message_fcm);
                        }
                    }
                }                
            }
        } catch(\Exception $e) {
            $admin = AdminModel::getAllContact();

            if($admin){
                foreach($admin as $dt){
                    $message = "[ADMIN] Hello $dt->username, there is an error in scheduler : weather_routine_fetch. Here's the detail :\n\n".$e->getMessage();

                    if($dt->telegram_user_id && $dt->telegram_is_valid == 1){
                        $response = Telegram::sendMessage([
                            'chat_id' => $dt->telegram_user_id,
                            'text' => $message,
                            'parse_mode' => 'HTML'
                        ]);
                    }
                }
            }
        }
    }
}
