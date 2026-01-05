<?php

namespace App\Helpers;
use Illuminate\Support\Collection;

class Document
{
    public static function documentDailyWeeklyReport($header_template,$style_template,$footer_template,$type,$date,$used_history,$wash_schedule,$weekly_schedule,$buyed_history,$add_wardrobe){ 
        $datetime = now();
        if($header_template == null){
            $header_template = Generator::getDocTemplate('header');
        }
        if($style_template == null){
            $style_template = Generator::getDocTemplate('style');
        }
        if($footer_template == null){
            $footer_template = Generator::getDocTemplate('footer');
        }

        function generateTable($title, $data) {
            $tbody = "";
            if(count($data) > 0){
                foreach ($data as $dt) {
                    $tbody .= "<tr>
                        <td>$dt->clothes_name</td>
                        <td>$dt->clothes_category</td>
                        <td>$dt->clothes_type</td>
                    </tr>";
                }
            } else {
                $tbody = "<tr><td colspan='3'><p class='text-secondary'>- No $title Found -</p></td></tr>";
            }
            
            return "<h6>$title</h6>
                <table>
                    <thead>
                        <tr>
                            <th>Clothes Name</th>
                            <th>Category</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>$tbody</tbody>
                </table>";
        }

        $tables = generateTable("Used History", $used_history).generateTable("Wash Schedule", $wash_schedule).generateTable("Weekly Schedule", $weekly_schedule).generateTable("Buyed History", $buyed_history).generateTable("Added To Wardrobe", $add_wardrobe);

        $html = "
            <html>
                <head>
                    $style_template
                </head>
                <body>
                    $header_template
                    <h3 style='margin:0 0 6px 0;'>Report : $type</h3>
                    <p style='margin:0; font-size:14px;'>Date : $date</p>
                    <p style='font-size:13px; text-align: justify;'>
                        At $datetime, this document has been generated for context of $type report for period <b>$date</b>. You can also import this document into Wardrobe Apps or send it to our Telegram Bot if you wish to analyze the items in this document for comparison with your clothes. 
                        Important to know, that this document is <b>accessible for everyone</b> by using this link. Here you can see the item in this report :
                    </p>
                    $tables
                    $footer_template
                </body>
            </html>";

        return $html;
    }

    public static function documentClothesDetail($header_template,$style_template,$footer_template,$clothes,$used_history,$wash_history,$last_used,$schedule,$outfit){
        $datetime = now();
        if($header_template == null){
            $header_template = Generator::getDocTemplate('header');
        }
        if($style_template == null){
            $style_template = Generator::getDocTemplate('style');
        }
        if($footer_template == null){
            $footer_template = Generator::getDocTemplate('footer');
        }

        $tbody_used_history = "";
        if(count($used_history) > 0){
            foreach ($used_history as $dt) {
                $tbody_used_history .= "<tr>
                    <td>$dt->used_context</td>
                    <td>$dt->clothes_note</td>
                    <td>$dt->created_at</td>
                </tr>";
            }
        } else {
            $tbody = "<tr><td colspan='3'><p class='text-secondary'>- No Used History Found -</p></td></tr>";
        }

        $tbody_schedule = "";
        if(count($schedule) > 0){
            foreach ($schedule as $dt) {
                $tbody_schedule .= "<tr>
                    <td>$dt->day</td>
                    <td>$dt->schedule_note</td>
                </tr>";
            }
        } else {
            $tbody = "<tr><td colspan='2'><p class='text-secondary'>- No Schedule Found -</p></td></tr>";
        }

        $tbody_outfit = "";
        if(count($outfit) > 0){
            foreach ($outfit as $dt) {
                $tbody_outfit .= "<tr>
                    <td>$dt->outfit_name</td>
                    <td>$dt->outfit_note</td>
                    <td>$dt->total_used</td>
                    <td>".$dt->last_used ?? '-'."</td>
                </tr>";
            }
        } else {
            $tbody = "<tr><td colspan='4'><p class='text-secondary'>- No Outfit Found -</p></td></tr>";
        }

        $clothes_image = $clothes->clothes_image ? "<img style='width:200px; height:200px;' src='$clothes->clothes_image'>" : '';
        $clothes_price = "Rp. ".$clothes->clothes_price ? number_format($clothes->clothes_price) : '-';

        $html = "
            <html>
                <head>
                    $style_template
                </head>
                <body>
                    $header_template
                    <h3 style='margin:0 0 6px 0;'>Clothes Detail</h3>
                    <p style='margin:0; font-size:14px;'>ID : $clothes->id</p>
                    <p style='font-size:13px; text-align: justify;'>
                        At $datetime, this document has been generated for context of clothes detail report. You can also import this document into Wardrobe Apps or send it to our Telegram Bot if you wish to copy the same detail and add new clothes with this properties. 
                        Important to know, that this document is <b>accessible for everyone</b> by using this link. Here you can see the item in this report :
                    </p>
                    $clothes_image
                    <h3>$clothes->clothes_name</h3>
                    <h6 style='margin-bottom:0;'>Category : <span style='font-weight:normal;'>$clothes->clothes_category</span></h6>
                    <h6 style='margin-bottom:0;'>Type : <span style='font-weight:normal;'>$clothes->clothes_type</span></h6>
                    <h6 style='margin-bottom:0;'>Merk : <span style='font-weight:normal;'>$clothes->clothes_merk</span></h6>
                    <h6 style='margin-bottom:0;'>Size : <span style='font-weight:normal;'>$clothes->clothes_size</span></h6>
                    <h6 style='margin-bottom:0;'>Price : <span style='font-weight:normal;'>$clothes_price</span></h6>
                    <h6 style='margin-bottom:0;'>Gender : <span style='font-weight:normal;'>$clothes->clothes_gender</span></h6>
                    <h6 style='margin-bottom:0;'>Made From : <span style='font-weight:normal;'>$clothes->clothes_made_from</span></h6>
                    <h6 style='margin-bottom:0;'>Qty : <span style='font-weight:normal;'>$clothes->clothes_qty</span></h6>
                    <h6 style='margin-bottom:0;'>Last Used : <span style='font-weight:normal;'>$last_used->created_at</span></h6>
                    <p style='margin-bottom:0;'>$clothes->clothes_desc</p>
                    <h6 style='margin-bottom:0;'>Used History</h6>
                    <table>
                        <thead>
                            <tr>
                                <th>Context</th>
                                <th>Notes</th>
                                <th>Used At</th>
                            </tr>
                        </thead>
                        <tbody>$tbody_used_history</tbody>
                    </table>
                    <h6>Schedule</h6>
                    <table>
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>$tbody_schedule</tbody>
                    </table>
                    <h6>Wash Schedule</h6>
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Notes</th>
                                <th>Checkpoint</th>
                                <th>Wash At</th>
                            </tr>
                        </thead>
                        <tbody>...</tbody>
                    </table>
                    <h6>Found In Outfit</h6>
                    <table>
                        <thead>
                            <tr>
                                <th>Outfit Name</th>
                                <th>Notes</th>
                                <th>Total Used</th>
                                <th>Last Used</th>
                            </tr>
                        </thead>
                        <tbody>$tbody_outfit</tbody>
                    </table>
                    $footer_template
                </body>
            </html>";

        return $html;
    }
}