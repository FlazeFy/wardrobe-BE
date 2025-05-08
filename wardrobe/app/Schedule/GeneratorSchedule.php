<?php

namespace App\Schedule;

use Carbon\Carbon;
use DateTime;
use Telegram\Bot\Laravel\Facades\Telegram;

use App\Models\ClothesModel;
use App\Models\ScheduleModel;
use App\Models\UserModel;

use App\Helpers\Formula;

class GeneratorSchedule
{
    public static function generate_outfit()
    {
        // $type = $request->clothes_type;
        // $temperature = $request->temperature ?? null;
        // $humidity = $request->humidity ?? null;
        // $weather = $request->weather ?? null;

        $temperature = null;
        $humidity = null;
        $weather = null;
        $day = date('l', strtotime('tomorrow'));

        $users = UserModel::getUserReadyGeneratedOutfit();

        if($users){
            foreach ($users as $user) {                
                if($user->telegram_is_valid == 1 && $user->telegram_user_id){
                    // Schedule Fetch
                    $scheduleIds = ScheduleModel::where('day', substr($day, 0, 3))->pluck('clothes_id')->toArray();

                    // Clothes Fetch
                    $query = ClothesModel::selectRaw('clothes_name, clothes_type, clothes_color, clothes_image,
                        MAX(clothes_used.created_at) as last_used,clothes_category,
                        CAST(SUM(CASE WHEN clothes_used.id IS NOT NULL THEN 1 ELSE 0 END) as UNSIGNED) as total_used')
                        ->leftJoin('clothes_used', 'clothes_used.clothes_id', '=', 'clothes.id')
                        ->whereNotIn('clothes_type', ['swimsuit', 'underwear', 'tie', 'belt'])
                        ->whereIn('clothes_category', ['upper_body', 'bottom_body', 'foot'])
                        ->where('clothes.created_by', $user->id)
                        ->where('has_washed', 1);
                    $clothes = $query->groupBy('clothes.id')->get();

                    $scored = [];
                    foreach ($clothes as $dt) {
                        $score = 0;

                        // If clothes found on today schedule
                        if (in_array($dt->id, $scheduleIds)) $score += 20;
                        $score += $dt->total_used ?? 0;

                        if ($dt->last_used) {
                            // If the clothes has been used. The more long last day used, the more high the score
                            $days = now()->diffInDays($dt->last_used);
                            $score += $days < 31 ? floor($days / 7) : floor($days / 30) + ($days % 30 > 0 ? 1 : 0);
                        } else {
                            // If the clothes never been used
                            $score += 20;
                        }

                        // Other formula
                        $score += Formula::getTemperatureScore($dt->clothes_type, $temperature);
                        $score += Formula::getHumidityScore($dt->clothes_type, $humidity);
                        $score += Formula::getWeatherScore($dt->clothes_type, $weather);
                        $score += Formula::getColorScore($dt->clothes_color, $dt->id);

                        $scored[] = array_merge($dt->toArray(), ['score' => $score]);
                    }

                    $final_res = collect($scored)
                        ->sortByDesc('score')
                        ->unique('clothes_category')
                        ->values();

                    $list_clothes = "";

                    foreach ($final_res as $dt) {
                        $list_clothes .= "- ".ucwords($dt['clothes_name'])." (".ucwords($dt['clothes_type'])." - ".ucwords($dt['clothes_color']).")\n";
                        if($dt['last_used']){
                            $list_clothes .= "Note: <i>This clothes is last used at ".date('Y-m-d',strtotime($dt['last_used']))." and has been used for ".$dt['total_used']." times</i>\n\n"; 
                        } else {
                            $list_clothes .= "\n"; 
                        }
                    }

                    $message = "Hello $user->username, we've just got you a suggestion for the tommorow outfit. Here are the details:\n\n$list_clothes";

                    $response = Telegram::sendMessage([
                        'chat_id' => $user->telegram_user_id,
                        'text' => $message,
                        'parse_mode' => 'HTML'
                    ]);
                }
            }
        }
    }
}
