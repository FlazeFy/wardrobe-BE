<?php

namespace App\Helpers;
use Telegram\Bot\Laravel\Facades\Telegram;

class Broadcast {

    public static function sendTelegramDoc($chat_id, $doc, $msg){
        Telegram::sendDocument([
            'chat_id' => $chat_id,
            'document' => $doc,
            'caption' => $msg,
            'parse_mode' => 'HTML',
        ]);
    }
}
