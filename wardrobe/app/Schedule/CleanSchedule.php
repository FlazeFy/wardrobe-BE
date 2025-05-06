<?php

namespace App\Schedule;

use Carbon\Carbon;
use DateTime;
use Telegram\Bot\Laravel\Facades\Telegram;

use App\Models\HistoryModel;
use App\Models\AdminModel;

class CleanSchedule
{
    public static function clean_history()
    {
        $days = 30;
        $total = HistoryModel::deleteHistoryForLastNDays($days);
        $admin = AdminModel::getAllContact();

        if($admin){
            foreach($admin as $dt){
                $message = "[ADMIN] Hello $dt->username, the system just run a clean history, with result of $total history executed";

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
