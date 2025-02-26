<?php
namespace App\Helpers;
use Illuminate\Support\Collection;

class Document
{
    public static function documentDailyWeeklyReport($header_template,$style_template,$footer_template,$type,$date,$used_history,$wash_schedule,$weekly_schedule,$buyed_history,$add_wardrobe){ 
        $tbody = "";
        $datetime = now();
        if($header_template == null){
            $header_template = Generator::generateDocTemplate('header');
        }
        if($style_template == null){
            $style_template = Generator::generateDocTemplate('style');
        }
        if($footer_template == null){
            $footer_template = Generator::generateDocTemplate('footer');
        }

        function generateTable($title, $data) {
            $tbody = "";
            foreach ($data as $dt) {
                $tbody .= "<tr>
                    <td>$dt->clothes_name</td>
                    <td>$dt->clothes_category</td>
                    <td>$dt->clothes_type</td>
                </tr>";
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
}