<?php

namespace App\Schedule;

use Carbon\Carbon;
use DateTime;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Helpers\Generator;

use App\Models\ClothesModel;
use App\Models\ScheduleModel;
use App\Models\QuestionModel;
use App\Models\AdminModel;

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
                    $list_clothes .= "- ".ucwords($dt->clothes_name)."$extra_desc\n";
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

    public static function remind_unwashed_clothes()
    {
        $clothes = ClothesModel::getUnwashedClothes();

        if($clothes){
            $user_before = "";
            $list_clothes = "";
            
            foreach ($clothes as $idx => $dt) {
                if ($user_before == "" || $user_before == $dt->username) {
                    $extra_desc = "";
                    if($dt->clothes_buy_at || $dt->is_favorite == 1 || $dt->is_scheduled == 1){
                        $extra_desc .= " (";
                    }

                    if($dt->clothes_buy_at){
                        $extra_desc .= "buy at $dt->clothes_buy_at";
                    }
                    if($dt->is_favorite == 1){
                        if($dt->clothes_buy_at){
                            $extra_desc .= ", ";
                        }
                        $extra_desc .= "is your favorited";
                    }
                    if($dt->is_scheduled == 1){
                        if($dt->clothes_buy_at || $dt->is_favorite == 1){
                            $extra_desc .= ", ";
                        }
                        $extra_desc .= "attached to schedule";
                    }

                    if($dt->clothes_buy_at || $dt->is_favorite == 1 || $dt->is_scheduled == 1){
                        $extra_desc .= ")";
                    }

                    $list_clothes .= "- ".ucwords($dt->clothes_name)."$extra_desc\n";
                }

                $next = $clothes[$idx + 1] ?? null;
                $is_last_or_diff_user = !$next || $next->username != $dt->username;

                if ($is_last_or_diff_user) {
                    $message = "Hello $dt->username, We're here to remind you. You have some clothes that has not been washed yet. Here are the details:\n\n$list_clothes";

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

    public static function remind_unironed_clothes()
    {
        $clothes = ClothesModel::getUnironedClothes();

        if($clothes){
            $user_before = "";
            $list_clothes = "";
            
            foreach ($clothes as $idx => $dt) {
                if ($user_before == "" || $user_before == $dt->username) {
                    $extra_desc = " (";

                    if($dt->is_favorite == 1){
                        $extra_desc .= "is your favorited";
                    }
                    if($dt->is_scheduled == 1){
                        if($dt->is_favorite == 1){
                            $extra_desc .= ", ";
                        }
                        $extra_desc .= "attached to schedule";
                    }

                    if($dt->is_favorite == 1 || $dt->is_scheduled == 1){
                        $extra_desc .= ", ";
                    }
                    if($dt->has_washed == 1){
                        $extra_desc .= "has";
                    } else {
                        $extra_desc .= "has'nt";
                    }
                    $extra_desc .= " been washed)";

                    $list_clothes .= "- ".ucwords($dt->clothes_name)."$extra_desc\n<i>Notes : made from $dt->clothes_made_from</i>\n\n";
                }

                $next = $clothes[$idx + 1] ?? null;
                $is_last_or_diff_user = !$next || $next->username != $dt->username;

                if ($is_last_or_diff_user) {
                    $message = "Hello $dt->username, We're here to remind you. You have some clothes that has not been ironed yet. We only suggest the clothes that is made from cotton, linen, silk, or rayon. Here are the details:\n\n$list_clothes";

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

    public static function remind_unused_clothes()
    {
        $days = 60;
        $clothes = ClothesModel::getUnusedClothes($days);

        if($clothes){
            $user_before = "";
            $list_clothes = "";
            
            foreach ($clothes as $idx => $dt) {
                if ($user_before == "" || $user_before == $dt->username) {
                    if($dt->total_used > 0){
                        $extra_desc = "Last used at ".date('Y-m-d',strtotime($dt->last_used));
                    } else {
                        $extra_desc = "Never been used";
                    }

                    $extra_space = "";
                    if($idx < count($clothes) - 1){
                        $extra_space = "\n\n";
                    }
                    $list_clothes .= "- ".ucwords($dt->clothes_name)." (".ucwords($dt->clothes_type).")\nNotes: <i>$extra_desc</i>$extra_space";
                }

                $next = $clothes[$idx + 1] ?? null;
                $is_last_or_diff_user = !$next || $next->username != $dt->username;

                if ($is_last_or_diff_user) {
                    $message = "Hello $dt->username, We're here to remind you. You have some clothes that has never been used since $days days after washed or being added to Wardrobe. Here are the details:\n\n$list_clothes\n\nUse and wash it again to keep your clothes at good quality and not smell musty";

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

    public static function remind_weekly_schedule()
    {
        $today = date('Y-m-d');
        $tomorrow = Carbon::parse($today)->addDay()->format('D');
        $clothes = ScheduleModel::getPlanSchedule($tomorrow);

        if($clothes){
            $user_before = "";
            $list_clothes = "";
            
            foreach ($clothes as $idx => $dt) {
                if ($user_before == "" || $user_before == $dt->username) {
                    $extra_desc = " (";

                    if($dt->is_favorite == 1){
                        $extra_desc .= "is your favorited, ";
                    }
                    if($dt->has_washed == 1){
                        $extra_desc .= "has";
                    } else {
                        $extra_desc .= "has'nt";
                    }
                    $extra_desc .= " been washed)";

                    $list_clothes .= "- ".ucwords($dt->clothes_name)." (".ucwords($dt->clothes_type).")\nNotes: <i>$extra_desc</i>";
                }

                $next = $clothes[$idx + 1] ?? null;
                $is_last_or_diff_user = !$next || $next->username != $dt->username;

                if ($is_last_or_diff_user) {
                    $message = "Hello $dt->username, We're here to remind you. You have some schedule for tommorow (".ucfirst($tomorrow).") to follow. Here are the details:\n\n$list_clothes";

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

    public static function remind_unanswered_question()
    {
        $question = QuestionModel::getUnansweredQuestion();

        if($question){
            $list_question = "";
            
            foreach ($question as $idx => $dt) {
                $list_question .= "- ".ucfirst($dt->question)."\nNotes: <i>ask at $dt->created_at</i>\n\n";
            }

            $admin = AdminModel::getAllContact();
            if($admin){
                foreach($admin as $dt){
                    $message = "[ADMIN] Hello $dt->username, We're here to remind you. You have some unanswered question that needed to be answer. Here are the details:\n\n$list_question";
    
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
