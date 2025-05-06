<?php

namespace App\Schedule;

use Carbon\Carbon;
use DateTime;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Helpers\Generator;

use App\Models\ClothesModel;

class ReminderSchedule
{
    public static function remind_predeleted_clothes()
    {
        $days = 30 ; // the total days must same with the one in clean deleted history
        $pre_remind_days = 3;
        $plan = ClothesModel::getClothesPrePlanDestroy($days - $pre_remind_days);

        if($plan){
            $user_before = "";
            $list_clothes = "";
            
            foreach ($plan as $idx => $dt) {
                if ($user_before == "" || $user_before == $dt->username) {
                    $extra_desc = "";
                    if($dt->total_outfit_attached > 0){
                        $extra_desc .= " (attached in $dt->total_outfit_attached outfit)";
                    }
                    $list_clothes .= "- $dt->clothes_name$extra_desc\n";
                }

                $next = $plan[$idx + 1] ?? null;
                $is_last_or_diff_user = !$next || $next->username != $dt->username;

                if ($is_last_or_diff_user) {
                    $message = "Hello $dt->username, We're here to remind you. That some of your clothes are set to deleted in $pre_remind_days days from now. Here are the details:\n\n$list_clothes";

                    if ($dt->telegram_user_id && $dt->telegram_is_valid == 1) {
                        Telegram::sendMessage([
                            'chat_id' => $dt->telegram_user_id,
                            'text' => $message,
                            'parse_mode' => 'HTML'
                        ]);
                    }

                    $list_clothes = "";
                }

                $user_before = $dt->username;
            }
        }
    }
}
