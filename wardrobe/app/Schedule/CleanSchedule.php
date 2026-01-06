<?php

namespace App\Schedule;
use Carbon\Carbon;
use DateTime;
use Telegram\Bot\Laravel\Facades\Telegram;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

// Model
use App\Models\ClothesModel;
use App\Models\ClothesUsedModel;
use App\Models\OutfitRelModel;
use App\Models\ScheduleModel;
use App\Models\WashModel;
use App\Models\HistoryModel;
use App\Models\AdminModel;
// Helper
use App\Helpers\Broadcast;

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
                    Broadcast::sendTelegramMessage($dt->telegram_user_id, $message);
                }
            }
        }
    }

    public static function clean_deleted_clothes()
    {
        $days = 30;
        $total_clothes = 0;
        $total_user = 0;
        $plan = ClothesModel::getClothesPlanDestroy($days);
        $admin = AdminModel::getAllContact();

        if($plan){
            $user_before = "";
            $list_clothes = "";

            foreach ($plan as $idx => $dt) {
                ClothesModel::destroy($dt->id);
                OutfitRelModel::hardDeleteOutfitRelByClothesId($dt->id);
                WashModel::hardDeleteWashByClothesId($dt->id);
                ScheduleModel::hardDeleteScheduleByClothesId($dt->id);
                ClothesUsedModel::hardDeleteClothesUsedByClothesId($dt->id);
                $total_clothes++;

                if ($user_before == "" || $user_before == $dt->username) {
                    $list_clothes .= "- ".ucwords($dt->clothes_name)."\n";
                }

                $next = $plan[$idx + 1] ?? null;
                $is_last_or_diff_user = !$next || $next->username != $dt->username;

                if ($is_last_or_diff_user) {
                    $message = "Hello $dt->username, We've recently cleaned up your deleted clothes. Here are the details:\n\n$list_clothes";

                    if ($dt->telegram_user_id && $dt->telegram_is_valid == 1) {
                        Broadcast::sendTelegramMessage($dt->telegram_user_id, $message);
                    }
                    if($dt->firebase_fcm_token){
                        $factory = (new Factory)->withServiceAccount(base_path('/firebase/wardrobe-26571-firebase-adminsdk-fint4-9966f0909b.json'));
                        $messaging = $factory->createMessaging();
                        $message_fcm = CloudMessage::withTarget('token', $dt->firebase_fcm_token)
                            ->withNotification(Notification::create($message, $dt->id));
                        $response = $messaging->send($message_fcm);
                    }

                    $list_clothes = "";
                    $total_user++;
                }

                $user_before = $dt->username;
            }
        }

        if($admin){
            foreach($admin as $dt){
                $message = "[ADMIN] Hello $dt->username, the system just run a clean deleted clothes, with result of $total_clothes clothes executed from $total_user user";

                if($dt->telegram_user_id && $dt->telegram_is_valid == 1){
                    Broadcast::sendTelegramMessage($dt->telegram_user_id, $message);
                }
            }
        }
    }
}
