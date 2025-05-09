<?php

namespace App\Schedule;

use Carbon\Carbon;
use DateTime;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;
use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\Canvas\Factory as CanvasFactory;
use Dompdf\Options as DompdfOptions;
use Dompdf\Adapter\CPDF;

use App\Helpers\Generator;
use App\Models\ClothesModel;
use App\Models\ClothesUsedModel;
use App\Models\ScheduleModel;
use App\Models\QuestionModel;
use App\Models\AdminModel;
use App\Models\UserTrackModel;

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
                    if($dt->firebase_fcm_token){
                        $factory = (new Factory)->withServiceAccount(base_path('/firebase/wardrobe-26571-firebase-adminsdk-fint4-9966f0909b.json'));
                        $messaging = $factory->createMessaging();
                        $message_fcm = CloudMessage::withTarget('token', $dt->firebase_fcm_token)
                            ->withNotification(Notification::create($message, $dt->firebase_fcm_token));
                        $response = $messaging->send($message_fcm);
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
                    if($dt->firebase_fcm_token){
                        $factory = (new Factory)->withServiceAccount(base_path('/firebase/wardrobe-26571-firebase-adminsdk-fint4-9966f0909b.json'));
                        $messaging = $factory->createMessaging();
                        $message_fcm = CloudMessage::withTarget('token', $dt->firebase_fcm_token)
                            ->withNotification(Notification::create($message, $dt->firebase_fcm_token));
                        $response = $messaging->send($message_fcm);
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
                    if($dt->firebase_fcm_token){
                        $factory = (new Factory)->withServiceAccount(base_path('/firebase/wardrobe-26571-firebase-adminsdk-fint4-9966f0909b.json'));
                        $messaging = $factory->createMessaging();
                        $message_fcm = CloudMessage::withTarget('token', $dt->firebase_fcm_token)
                            ->withNotification(Notification::create($message, $dt->firebase_fcm_token));
                        $response = $messaging->send($message_fcm);
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
                    if($dt->firebase_fcm_token){
                        $factory = (new Factory)->withServiceAccount(base_path('/firebase/wardrobe-26571-firebase-adminsdk-fint4-9966f0909b.json'));
                        $messaging = $factory->createMessaging();
                        $message_fcm = CloudMessage::withTarget('token', $dt->firebase_fcm_token)
                            ->withNotification(Notification::create($message, $dt->firebase_fcm_token));
                        $response = $messaging->send($message_fcm);
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

                    if($dt->firebase_fcm_token){
                        $factory = (new Factory)->withServiceAccount(base_path('/firebase/wardrobe-26571-firebase-adminsdk-fint4-9966f0909b.json'));
                        $messaging = $factory->createMessaging();
                        $message_fcm = CloudMessage::withTarget('token', $dt->firebase_fcm_token)
                            ->withNotification(Notification::create($message, $dt->firebase_fcm_token));
                        $response = $messaging->send($message_fcm);
                    }
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
            $audit_tbody = "";
            
            foreach ($question as $idx => $dt) {
                $list_question .= "- ".ucfirst($dt->question)."\nNotes: <i>ask at $dt->created_at</i>\n\n";

                $audit_tbody .= "
                    <tr>
                        <td style='width: 140px; text-align:center;'>$dt->created_at</td>
                        <td>".ucfirst($dt->question)."</td>
                        <td style='width: 450px;'></td>
                    </tr>
                ";
            }

            $admin = AdminModel::getAllContact();
            if($admin){
                $datetime = date("Y-m-d H:i:s");    
                $options = new DompdfOptions();
                $options->set('defaultFont', 'Helvetica');
                $dompdf = new Dompdf($options);
                $header_template = Generator::getDocTemplate('header');
                $style_template = Generator::getDocTemplate('style');
                $footer_template = Generator::getDocTemplate('footer');

                $html = "
                <html>
                    <head>
                        $style_template
                    </head>
                    <body>
                        $header_template
                        <h2>Reminder - Unanswered Question</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th style='width: 140px;'>Date</th>
                                    <th>Question</th>
                                    <th>Answer</th>
                                </tr>
                            </thead>
                            <tbody>$audit_tbody</tbody>
                        </table>
                        $footer_template
                    </body>
                </html>";
        
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
        
                $pdfContent = $dompdf->output();
                $pdfFilePath = public_path("reminder_unanswered_question_$datetime.pdf");
                file_put_contents($pdfFilePath, $pdfContent);
                $inputFile = InputFile::create($pdfFilePath, $pdfFilePath);

                foreach($admin as $dt){
                    $message = "[ADMIN] Hello $dt->username, We're here to remind you. You have some unanswered question that needed to be answer. Here are the details:\n\n$list_question";
    
                    if($dt->telegram_user_id && $dt->telegram_is_valid == 1){
                        $response = Telegram::sendDocument([
                            'chat_id' => $dt->telegram_user_id,
                            'document' => $inputFile,
                            'caption' => $message,
                            'parse_mode' => 'HTML'
                        ]);
                    }
                }

                unlink($pdfFilePath);
            }
        }
    }

    public static function remind_old_last_track()
    {
        $days = 5;
        $old_track = UserTrackModel::getOldLastTrack($days);

        if($old_track){            
            foreach ($old_track as $dt) {
                $message = "Hello ".$dt['username'].", We've noticed that your last location record when using Wardrobe is at ".date("Y-m-d H:i",strtotime($dt['last_track'])).".\n\nKeep update your location via opened the Wardrobe Web or Mobile version. Or maybe just send your current location via Wardrobe Telegram BOT.";
    
                if($dt['telegram_user_id'] && $dt['telegram_is_valid'] == 1){
                    $response = Telegram::sendMessage([
                        'chat_id' => $dt['telegram_user_id'],
                        'text' => $message,
                        'parse_mode' => 'HTML'
                    ]);
                }
            }
        }
    }

    public static function remind_wash_used_clothes(){
        $days = 7;
        $clothes = ClothesUsedModel::getUsedClothesReadyToWash($days);

        if($clothes){          
            $user_before = "";
            $list_clothes = "";

            foreach ($clothes as $idx => $dt) {
                if ($user_before == "" || $user_before == $dt->username) {
                    $extra_desc = "";

                    if($dt->is_scheduled == 1){
                        $extra_desc .= "is on scheduled!";
                    }
                    if($dt->is_faded == 1){
                        if($dt->is_scheduled == 1){
                            $extra_desc .= ", ";
                        }
                        $extra_desc .= "is faded!";
                    }
                    if($dt->is_scheduled == 1 || $dt->is_faded){
                        $extra_desc = ", $extra_desc";
                    }

                    $list_clothes .= "- <b>".ucwords($dt->clothes_name)."</b> (".ucwords($dt->clothes_type)." - ".ucwords($dt->clothes_made_from).")\n<i>Used Context: $dt->used_context\nNotes: Last used at ".date("Y-m-d",strtotime($dt->created_at))."$extra_desc</i>\n\n";
                }

                $next = $clothes[$idx + 1] ?? null;
                $is_last_or_diff_user = !$next || $next->username != $dt->username;

                if ($is_last_or_diff_user) {
                    $message = "Hello $dt->username, We've noticed that some of your clothes are not washed after being used after $days days from now. Don't forget to wash your used clothes, here's the detail:\n\n$list_clothes";
    
                    if($dt->telegram_user_id && $dt->telegram_is_valid == 1){
                        $response = Telegram::sendMessage([
                            'chat_id' => $dt->telegram_user_id,
                            'text' => $message,
                            'parse_mode' => 'HTML'
                        ]);
                    }
                    if($dt->firebase_fcm_token){
                        $factory = (new Factory)->withServiceAccount(base_path('/firebase/wardrobe-26571-firebase-adminsdk-fint4-9966f0909b.json'));
                        $messaging = $factory->createMessaging();
                        $message_fcm = CloudMessage::withTarget('token', $dt->firebase_fcm_token)
                            ->withNotification(Notification::create($message, $dt->firebase_fcm_token));
                        $response = $messaging->send($message_fcm);
                    }

                    $list_clothes = "";
                }

                $user_before = $dt->username;
            }
        }
    }
}
