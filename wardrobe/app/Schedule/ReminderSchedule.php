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

// Helper
use App\Helpers\Generator;
use App\Helpers\Broadcast;
// Model
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

        // Get clothes who ready to permanently deleted
        $plan = ClothesModel::getClothesPrePlanDestroy($days - $pre_remind_days);
        if($plan){
            $user_before = "";
            $list_clothes = "";
            
            foreach ($plan as $idx => $dt) {
                // Prepare sentece of list clothes
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

                    // Broadcast Telegram
                    if ($dt->telegram_user_id && $dt->telegram_is_valid == 1) {
                        Broadcast::sendTelegramMessage($dt->telegram_user_id, $message);
                    }

                    // Broadcast FCM Notification
                    if($dt->firebase_fcm_token){
                        $factory = (new Factory)->withServiceAccount(base_path('/firebase/wardrobe-26571-firebase-adminsdk-fint4-9966f0909b.json'));
                        $messaging = $factory->createMessaging();
                        $message_fcm = CloudMessage::withTarget('token', $dt->firebase_fcm_token)->withNotification(Notification::create($message, $dt->firebase_fcm_token));
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
        // Get unwashed clothes
        $clothes = ClothesModel::getUnwashedClothes();
        if($clothes){
            $user_before = "";
            $list_clothes = "";
            
            foreach ($clothes as $idx => $dt) {
                // Prepare sentece of list clothes
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

                    // Broadcast Telegram
                    if ($dt->telegram_user_id && $dt->telegram_is_valid == 1) {
                        Broadcast::sendTelegramMessage($dt->telegram_user_id, $message);
                    }

                    // Broadcast FCM Notification
                    if($dt->firebase_fcm_token){
                        $factory = (new Factory)->withServiceAccount(base_path('/firebase/wardrobe-26571-firebase-adminsdk-fint4-9966f0909b.json'));
                        $messaging = $factory->createMessaging();
                        $message_fcm = CloudMessage::withTarget('token', $dt->firebase_fcm_token)->withNotification(Notification::create($message, $dt->firebase_fcm_token));
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
        // Get unironed clothes 
        $clothes = ClothesModel::getUnironedClothes();
        if($clothes){
            $user_before = "";
            $list_clothes = "";
            
            foreach ($clothes as $idx => $dt) {
                // Prepare sentece of list clothes
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

                    // Broadcast Telegram
                    if ($dt->telegram_user_id && $dt->telegram_is_valid == 1) {
                        Broadcast::sendTelegramMessage($dt->telegram_user_id, $message);
                    }

                    // Broadcast FCM Notification
                    if($dt->firebase_fcm_token){
                        $factory = (new Factory)->withServiceAccount(base_path('/firebase/wardrobe-26571-firebase-adminsdk-fint4-9966f0909b.json'));
                        $messaging = $factory->createMessaging();
                        $message_fcm = CloudMessage::withTarget('token', $dt->firebase_fcm_token)->withNotification(Notification::create($message, $dt->firebase_fcm_token));
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

        // Get unused clothes
        $clothes = ClothesModel::getUnusedClothes($days);
        if($clothes){
            $user_before = "";
            $list_clothes = "";
            
            foreach ($clothes as $idx => $dt) {
                // Prepare sentece of list clothes
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

                    // Broadcast Telegram
                    if ($dt->telegram_user_id && $dt->telegram_is_valid == 1) {
                        Broadcast::sendTelegramMessage($dt->telegram_user_id, $message);
                    }

                    // Broadcast FCM Notification
                    if($dt->firebase_fcm_token){
                        $factory = (new Factory)->withServiceAccount(base_path('/firebase/wardrobe-26571-firebase-adminsdk-fint4-9966f0909b.json'));
                        $messaging = $factory->createMessaging();
                        $message_fcm = CloudMessage::withTarget('token', $dt->firebase_fcm_token)->withNotification(Notification::create($message, $dt->firebase_fcm_token));
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

        // Get schedule by date
        $clothes = ScheduleModel::getPlanSchedule($tomorrow);
        if($clothes){
            $user_before = "";
            $list_clothes = "";
            
            foreach ($clothes as $idx => $dt) {
                // Prepare sentece of list clothes
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

                    // Broadcast Telegram
                    if ($dt->telegram_user_id && $dt->telegram_is_valid == 1) {
                        Broadcast::sendTelegramMessage($dt->telegram_user_id, $message);
                    }

                    $list_clothes = "";

                    // Broadcast FCM Notification
                    if($dt->firebase_fcm_token){
                        $factory = (new Factory)->withServiceAccount(base_path('/firebase/wardrobe-26571-firebase-adminsdk-fint4-9966f0909b.json'));
                        $messaging = $factory->createMessaging();
                        $message_fcm = CloudMessage::withTarget('token', $dt->firebase_fcm_token)->withNotification(Notification::create($message, $dt->firebase_fcm_token));
                        $response = $messaging->send($message_fcm);
                    }
                }

                $user_before = $dt->username;
            }
        }
    }

    public static function remind_unanswered_question()
    {
        // Get question that still not been answered
        $question = QuestionModel::getUnansweredQuestion();
        if($question){
            $list_question = "";
            $audit_tbody = "";
            
            // Build table body (content)
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

            // Get admin contact 
            $admin = AdminModel::getAllContact();
            if($admin){
                // Prepare document config
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
        
                // Render docs
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                $pdfContent = $dompdf->output();
                
                // Create a temporary file
                $tmpFilePath = tempnam(sys_get_temp_dir(), 'pdf_');
                file_put_contents($tmpFilePath, $pdfContent);

                // Wrap it as InputFile with correct filename
                $inputFile = InputFile::create($tmpFilePath, $file_name);

                foreach($admin as $dt){    
                    // Send telegram message with the file
                    if($dt->telegram_user_id && $dt->telegram_is_valid == 1){
                        $message = "[ADMIN] Hello $dt->username, We're here to remind you. You have some unanswered question that needed to be answer. Here are the details:\n\n$list_question";
                        Broadcast::sendTelegramDoc($dt->telegram_user_id, $inputFile, $message);
                    }
                }

                // Clean up temp file
                unlink($tmpFilePath);
            }
        }
    }

    public static function remind_old_last_track()
    {
        $days = 5;

        // Get user track that has passed n days
        $old_track = UserTrackModel::getOldLastTrack($days);
        if($old_track){            
            foreach ($old_track as $dt) {
                $message = "Hello ".$dt['username'].", We've noticed that your last location record when using Wardrobe is at ".date("Y-m-d H:i",strtotime($dt['last_track'])).".\n\nKeep update your location via opened the Wardrobe Web or Mobile version. Or maybe just send your current location via Wardrobe Telegram BOT.";
    
                // Broadcast Telegram
                if($dt['telegram_user_id'] && $dt['telegram_is_valid'] == 1){
                    Broadcast::sendTelegramMessage($dt['telegram_user_id'], $message);
                }
            }
        }
    }

    public static function remind_wash_used_clothes(){
        $days = 7;

        // Get used clothes that ready to wash
        $clothes = ClothesUsedModel::getUsedClothesReadyToWash($days);
        if($clothes){          
            $user_before = "";
            $list_clothes = "";

            foreach ($clothes as $idx => $dt) {
                // Prepare sentece of list clothes
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
                    if($dt->is_scheduled == 1 || $dt->is_faded == 1){
                        $extra_desc = ", $extra_desc";
                    }

                    $list_clothes .= "- <b>".ucwords($dt->clothes_name)."</b> (".ucwords($dt->clothes_type)." - ".ucwords($dt->clothes_made_from).")\n<i>Used Context: $dt->used_context\nNotes: Last used at ".date("Y-m-d",strtotime($dt->created_at))."$extra_desc</i>\n\n";
                }

                $next = $clothes[$idx + 1] ?? null;
                $is_last_or_diff_user = !$next || $next->username != $dt->username;

                if ($is_last_or_diff_user) {
                    $message = "Hello $dt->username, We've noticed that some of your clothes are not washed after being used after $days days from now. Don't forget to wash your used clothes, here's the detail:\n\n$list_clothes";
    
                    // Broadcast Telegram
                    if($dt->telegram_user_id && $dt->telegram_is_valid == 1){
                        Broadcast::sendTelegramMessage($dt->telegram_user_id, $message);
                    }

                    // Broadcast FCM Notification
                    if($dt->firebase_fcm_token){
                        $factory = (new Factory)->withServiceAccount(base_path('/firebase/wardrobe-26571-firebase-adminsdk-fint4-9966f0909b.json'));
                        $messaging = $factory->createMessaging();
                        $message_fcm = CloudMessage::withTarget('token', $dt->firebase_fcm_token)->withNotification(Notification::create($message, $dt->firebase_fcm_token));
                        $response = $messaging->send($message_fcm);
                    }

                    $list_clothes = "";
                }

                $user_before = $dt->username;
            }
        }
    }
}
