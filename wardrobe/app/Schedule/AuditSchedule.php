<?php

namespace App\Schedule;

use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Mail;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;
use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\Canvas\Factory as CanvasFactory;
use Dompdf\Options as DompdfOptions;
use Dompdf\Adapter\CPDF;

use App\Helpers\Generator;

use App\Models\ErrorModel;
use App\Models\AdminModel;

class AuditSchedule
{
    public static function audit_error()
    {
        $summary = ErrorModel::getAllErrorAudit();
        
        if($summary){
            $audit = "";
            $total = count($summary);

            foreach($summary as $dt){
                $audit .= "
                    <tr>
                        <td>$dt->message</td>
                        <td style='text-align:center;'>$dt->created_at</td>
                        <td style='text-align:center;'>";
                        if($dt->faced_by){
                            $audit .= $dt->faced_by;
                        } else {
                            $audit .= "-";
                        }
                        $audit.= "</td>
                        <td style='text-align:center;'>$dt->total</td>
                    </tr>
                ";
            }
            
            $admin = AdminModel::getAllContact();
            $datetime = date("Y-m-d H:i:s");    
            $options = new DompdfOptions();
            $options->set('defaultFont', 'Helvetica');
            $dompdf = new Dompdf($options);
            $header_template = Generator::generateDocTemplate('header');
            $style_template = Generator::generateDocTemplate('style');
            $footer_template = Generator::generateDocTemplate('footer');
    
            $html = "
            <html>
                <head>
                    $style_template
                </head>
                <body>
                    $header_template
                    <h2>Audit - Error</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Error Message</th>
                                <th>Datetime</th>
                                <th>Faced By</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>$audit</tbody>
                    </table>
                    $footer_template
                </body>
            </html>";
    
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
    
            $pdfContent = $dompdf->output();
            $pdfFilePath = public_path("audit_error_$datetime.pdf");
            file_put_contents($pdfFilePath, $pdfContent);
            $inputFile = InputFile::create($pdfFilePath, $pdfFilePath);

            foreach($admin as $dt){
                $message = "[ADMIN] Hello $dt->username, the system just run an audit error, with result of $total error found. Here's the document";
                
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

    public static function audit_apps(){
        $days = 7;
        $summary = AdminModel::getAppsSummaryForLastNDays($days);

        if($summary){
            $admin = AdminModel::getAllContact();

            foreach($admin as $dt){
                $message_template = "[ADMIN] Hello $dt->username, here's the apps summary for the last $days days:";
                $message = "$message_template\n\n- Clothes Created: $summary->clothes_created\n- Outfit Generated: $summary->outfit_generated\n- Wash Created: $summary->wash_created\n- Clothes Used: $summary->clothes_used\n- New User : $summary->new_user\n- Question Created : $summary->question_created\n- Error Happen : $summary->error_happen";

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
