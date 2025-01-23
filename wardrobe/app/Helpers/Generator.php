<?php
namespace App\Helpers;

class Generator
{
    public static function getUUID(){
        $result = '';
        $bytes = random_bytes(16);
        $hex = bin2hex($bytes);
        $time_low = substr($hex, 0, 8);
        $time_mid = substr($hex, 8, 4);
        $time_hi_and_version = substr($hex, 12, 4);
        $clock_seq_hi_and_reserved = hexdec(substr($hex, 16, 2)) & 0x3f;
        $clock_seq_low = hexdec(substr($hex, 18, 2));
        $node = substr($hex, 20, 12);
        $uuid = sprintf('%s-%s-%s-%02x%02x-%s', $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $clock_seq_low, $node);
        
        return $uuid;
    }

    public static function getMessageTemplate($type, $ctx){
        if (in_array($type, ['create', 'update', 'delete', 'permentally delete', 'fetch','recover','analyze','generate','remove'])) {
            $ext = in_array($type, ['fetch','recover']) ? "ed" : "d";
            $res = "$ctx ".$type.$ext;           
        } else if($type == "not_found"){
            $res = "$ctx not found";
        } else if($type == "unknown_error"){
            $res = "something wrong. please contact admin";
        } else if($type == "conflict"){
            $res = "$ctx has been used. try another";
        } else if($type == "custom"){
            $res = "$ctx";
        } else if($type == "validation_failed"){
            $res = "validation failed : $ctx";
        } else if($type == "permission"){
            $res = "permission denied. only $ctx can use this feature";
        } else {
            $res = "failed to get respond message";
        }

        return $res;
    }

    public static function getRandomDate($null){
        if($null == 0){
            $start = strtotime('2023-01-01 00:00:00');
            $end = strtotime(date("Y-m-d H:i:s"));
            $random = mt_rand($start, $end); 
            $res = date('Y-m-d H:i:s', $random);
        } else {
            $res = null;
        }

        return $res;
    }

    public static function getRandomTimezone(){
        $symbol = ['+','-'];
        $ran = mt_rand(0, 1);
        $select_symbol = $symbol[$ran];
        if($select_symbol == '+'){
            $hour = mt_rand(0, 14);
        } else {
            $hour = mt_rand(0, 12);
        }

        $timezone = "$select_symbol$hour:00";
        return $timezone;
    }

    public static function getRandomSeed($type){
        if($type == 'clothes_size'){
            $seed = ['S','M','L','XL','XXL','XXL'];
        } else if($type == 'clothes_gender'){
            $seed = ['male','female','unisex'];
        } else if($type == 'clothes_category'){
            $seed = ['upper_body','bottom_body','head','foot','hand'];
        } else if($type == 'wash_type'){
            $seed = ['laundry','self'];
        }

        $ran = mt_rand(0, count($seed)-1);

        return $seed[$ran];
    }

    public static function generateDocTemplate($type){
        $datetime = now();

        if($type == "footer"){
            return "
                <br><hr>
                <div>
                    <h6 class='date-text' style='margin: 0;'>Parts of FlazenApps</h6>
                    <h6 class='date-text' style='margin: 0; float:right; margin-top:-12px;'>Generated at $datetime by <span style='color:#3b82f6;'>https://wardrobe.leonardhors.com</span></h6>
                </div>
            ";
        } else if($type == "header"){
            return "
                <div style='text-align:center;'>
                    <h1 style='color:#3b82f6; margin:0;'>Wardrobe</h1>
                    <h4 style='color:#212121; margin:0; font-style:italic;'>Effortless style decision and Organize</div>
                <hr>
            ";
        } else if($type == "style"){
            return "
                <style>
                    body { font-family: Helvetica; }
                    table { border-collapse: collapse; font-size:10px; width:100%; }
                    td, th { border: 1px solid #dddddd; text-align: left; padding: 8px; }
                    th { text-align:center; }
                    .date-text { font-style:italic; font-weight:normal; color:grey; font-size:11px; }
                    thead { background-color:rgba(59, 131, 246, 0.75); }
                </style>
            ";
        }
    }
}