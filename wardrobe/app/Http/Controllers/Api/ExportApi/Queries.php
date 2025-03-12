<?php

namespace App\Http\Controllers\Api\ExportApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\Canvas\Factory as CanvasFactory;
use Dompdf\Options as DompdfOptions;
use Dompdf\Adapter\CPDF;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;
use Carbon\Carbon;

// Models
use App\Models\ClothesModel;
use App\Models\UserModel;
use App\Models\ClothesUsedModel;
use App\Models\ScheduleModel;
use App\Models\WashModel;
use App\Models\HistoryModel;
use App\Models\OutfitRelModel;

// Export
use App\Exports\CalendarClothesExport;

// Helper
use App\Helpers\Generator;
use App\Helpers\Document;

class Queries extends Controller
{
    public function get_export_clothes_excel(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $datetime = date('Y-m-d_H-i-s');
            $user = UserModel::getProfile($user_id);
            $file_name = "clothes-$user->username-$datetime.xlsx";
    
            $active_clothes = ClothesModel::getClothesExport($user_id, 'active');
            $deleted_clothes = ClothesModel::getClothesExport($user_id, 'deleted');
    
            Excel::store(new class($active_clothes, $deleted_clothes) implements WithMultipleSheets {
                private $activeClothes;
                private $deletedClothes;
    
                public function __construct($activeClothes, $deletedClothes)
                {
                    $this->activeClothes = $activeClothes;
                    $this->deletedClothes = $deletedClothes;
                }
    
                public function sheets(): array
                {
                    return [
                        new class($this->activeClothes) implements FromCollection, WithHeadings, WithTitle {
                            private $data;
                            
                            public function __construct($data)
                            {
                                $this->data = $data;
                            }
                            public function collection()
                            {
                                return $this->data;
                            }
                            public function headings(): array
                            {
                                return ['id', 'clothes_name', 'clothes_desc', 'clothes_merk', 'clothes_size', 'clothes_gender', 'clothes_made_from', 'clothes_color', 'clothes_category', 'clothes_type', 'clothes_price', 'clothes_buy_at', 'clothes_qty', 'clothes_image', 'is_faded', 'has_washed', 'has_ironed', 'is_favorite', 'is_scheduled', 'created_at', 'updated_at'];
                            }
                            public function title(): string
                            {
                                return "Active Clothes";
                            }
                        },
                        new class($this->deletedClothes) implements FromCollection, WithHeadings, WithTitle {
                            private $data;
    
                            public function __construct($data)
                            {
                                $this->data = $data;
                            }
                            public function collection()
                            {
                                return $this->data;
                            }
                            public function headings(): array
                            {
                                return ['id', 'clothes_name', 'clothes_desc', 'clothes_merk', 'clothes_size', 'clothes_gender', 'clothes_made_from', 'clothes_color', 'clothes_category', 'clothes_type', 'clothes_price', 'clothes_buy_at', 'clothes_qty', 'clothes_image', 'is_faded', 'has_washed', 'has_ironed', 'is_favorite', 'is_scheduled', 'created_at', 'updated_at', 'deleted_at'];
                            }
                            public function title(): string
                            {
                                return "Deleted Clothes";
                            }
                        }
                    ];
                }
            }, $file_name, 'public');
    
            $storagePath = storage_path("app/public/$file_name");
            $publicPath = public_path($file_name);
            if (!file_exists($storagePath)) {
                throw new \Exception("File not found: $storagePath");
            }
            copy($storagePath, $publicPath);
    
            if ($user && $user->telegram_is_valid == 1 && $user->telegram_user_id) {
                $inputFile = InputFile::create($publicPath, $file_name);
    
                Telegram::sendDocument([
                    'chat_id' => $user->telegram_user_id,
                    'document' => $inputFile,
                    'caption' => "Your clothes export is ready",
                    'parse_mode' => 'HTML',
                ]);
            }
    
            return response()->download($publicPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_export_clothes_used_excel(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $datetime = date('Y-m-d_H-i-s');
            $user = UserModel::getProfile($user_id);
            $file_name = "clothes_used-$user->username-$datetime.xlsx";

            $clothes_used = ClothesUsedModel::getClothesUsedExport($user_id);

            Excel::store(new class($clothes_used) implements WithMultipleSheets {
                private $clothes_used;

                public function __construct($clothes_used)
                {
                    $this->usedClothes = $clothes_used;
                }

                public function sheets(): array
                {
                    return [
                        new class($this->usedClothes) implements FromCollection, WithHeadings, WithTitle {
                            private $data;

                            public function __construct($data)
                            {
                                $this->data = $data;
                            }
                            public function collection()
                            {
                                return $this->data;
                            }
                            public function headings(): array
                            {
                                return ['clothes_name', 'clothes_note', 'used_context', 'clothes_merk', 'clothes_made_from', 'clothes_color', 'clothes_type', 'is_favorite', 'used_at'];
                            }
                            public function title(): string
                            {
                                return "Clothes History";
                            }
                        }
                    ];
                }
            }, $file_name, 'public');
    
            $storagePath = storage_path("app/public/$file_name");
            $publicPath = public_path($file_name);
            if (!file_exists($storagePath)) {
                throw new \Exception("File not found: $storagePath");
            }
            copy($storagePath, $publicPath);
    
            if ($user && $user->telegram_is_valid == 1 && $user->telegram_user_id) {
                $inputFile = InputFile::create($publicPath, $file_name);
    
                Telegram::sendDocument([
                    'chat_id' => $user->telegram_user_id,
                    'document' => $inputFile,
                    'caption' => "Your clothes used export is ready",
                    'parse_mode' => 'HTML',
                ]);
            }
    
            return response()->download($publicPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_export_wash_excel(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $datetime = date('Y-m-d_H-i-s');
            $user = UserModel::getProfile($user_id);
            $file_name = "wash_history-$user->username-$datetime.xlsx";

            $wash_history = WashModel::getWashExport($user_id)->map(function ($col) {
                unset($col->id);
                return $col;
            });

            Excel::store(new class($wash_history) implements WithMultipleSheets {
                private $wash_history;

                public function __construct($wash_history)
                {
                    $this->washClothes = $wash_history;
                }

                public function sheets(): array
                {
                    return [
                        new class($this->washClothes) implements FromCollection, WithHeadings, WithTitle {
                            private $data;

                            public function __construct($data)
                            {
                                $this->data = $data;
                            }
                            public function collection()
                            {
                                return $this->data;
                            }
                            public function headings(): array
                            {
                                return ['clothes_name', 'wash_type', 'wash_note', 'wash_checkpoint', 'clothes_merk', 'clothes_made_from', 'clothes_color', 'clothes_type', 'wash_at', 'finished_at'];
                            }
                            public function title(): string
                            {
                                return "Wash History";
                            }
                        }
                    ];
                }
            }, $file_name, 'public');
        
            $storagePath = storage_path("app/public/$file_name");
            $publicPath = public_path($file_name);
            if (!file_exists($storagePath)) {
                throw new \Exception("File not found: $storagePath");
            }
            copy($storagePath, $publicPath);

            if ($user && $user->telegram_is_valid == 1 && $user->telegram_user_id) {
                $inputFile = InputFile::create($publicPath, $file_name);

                Telegram::sendDocument([
                    'chat_id' => $user->telegram_user_id,
                    'document' => $inputFile,
                    'caption' => "Your wash history export is ready",
                    'parse_mode' => 'HTML',
                ]);
            }

            return response()->download($publicPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_export_history_excel(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $datetime = date('Y-m-d_H-i-s');
            $user = UserModel::getProfile($user_id);
            $file_name = "history-$user->username-$datetime.xlsx";

            $history = HistoryModel::getHistoryExport($user_id);

            Excel::store(new class($history) implements WithMultipleSheets {
                private $history;

                public function __construct($history)
                {
                    $this->history = $history;
                }

                public function sheets(): array
                {
                    return [
                        new class($this->history) implements FromCollection, WithHeadings, WithTitle {
                            private $data;

                            public function __construct($data)
                            {
                                $this->data = $data;
                            }
                            public function collection()
                            {
                                return $this->data;
                            }
                            public function headings(): array
                            {
                                return ['history_type', 'history_context', 'created_at'];
                            }
                            public function title(): string
                            {
                                return "Apps History";
                            }
                        }
                    ];
                }
            }, $file_name, 'public');
        
            $storagePath = storage_path("app/public/$file_name");
            $publicPath = public_path($file_name);
            if (!file_exists($storagePath)) {
                throw new \Exception("File not found: $storagePath");
            }
            copy($storagePath, $publicPath);

            if ($user && $user->telegram_is_valid == 1 && $user->telegram_user_id) {
                $inputFile = InputFile::create($publicPath, $file_name);

                Telegram::sendDocument([
                    'chat_id' => $user->telegram_user_id,
                    'document' => $inputFile,
                    'caption' => "Your apps history export is ready",
                    'parse_mode' => 'HTML',
                ]);
            }

            return response()->download($publicPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_export_clothes_calendar_excel(Request $request, $year){
        try {
            $user_id = $request->user()->id;
            $datetime = date('Y-m-d_H-i-s');
            $user = UserModel::getProfile($user_id);
            $file_name = "calendar-$year-$user->username-$datetime.xlsx";

            $res_used_history = ClothesUsedModel::getClothesUsedHistoryCalendar($user_id, $year, null)->get()->map(function($col) {
                return [
                    'date' => Carbon::parse($col->created_at)->format('Y-m-d'),
                    'clothes_name' => $col->clothes_name,
                    'clothes_type' => $col->clothes_type
                ];
            });
            $res_wash_schedule = WashModel::getWashCalendar($user_id, $year, null)->get()->map(function($col) {
                return [
                    'date' => Carbon::parse($col->created_at)->format('Y-m-d'),
                    'clothes_name' => $col->clothes_name,
                    'clothes_type' => $col->clothes_type
                ];
            });
            $res_buyed_history = ClothesModel::getClothesBuyedCalendar($user_id, $year, null)->get()->map(function($col) {
                return [
                    'date' => Carbon::parse($col->created_at)->format('Y-m-d'),
                    'clothes_name' => $col->clothes_name,
                    'clothes_type' => $col->clothes_type
                ];
            });
            $res_add_wardrobe = ClothesModel::getClothesCreatedCalendar($user_id, $year, null)->get()->map(function($col) {
                return [
                    'date' => Carbon::parse($col->created_at)->format('Y-m-d'),
                    'clothes_name' => $col->clothes_name,
                    'clothes_type' => $col->clothes_type
                ];
            });

            Excel::store(new class($res_used_history, $res_wash_schedule, $res_buyed_history, $res_add_wardrobe) implements WithMultipleSheets {
                private $res_used_history;
                private $res_wash_schedule;
                private $res_buyed_history;
                private $res_add_wardrobe;

                public function __construct($res_used_history, $res_wash_schedule, $res_buyed_history, $res_add_wardrobe)
                {
                    $this->res_used_history = $res_used_history;
                    $this->res_wash_schedule = $res_wash_schedule;
                    $this->res_buyed_history = $res_buyed_history;
                    $this->res_add_wardrobe = $res_add_wardrobe;
                }

                public function sheets(): array
                {
                    return [
                        new CalendarClothesExport($this->res_used_history, 'Used History'), 
                        new CalendarClothesExport($this->res_wash_schedule, 'Wash Schedule'),
                        new CalendarClothesExport($this->res_buyed_history, 'Buyed History'), 
                        new CalendarClothesExport($this->res_add_wardrobe, 'Add Wardrobe'),
                    ];
                }
            }, $file_name, 'public');
        
            $storagePath = storage_path("app/public/$file_name");
            $publicPath = public_path($file_name);
            if (!file_exists($storagePath)) {
                throw new \Exception("File not found: $storagePath");
            }
            copy($storagePath, $publicPath);

            if ($user && $user->telegram_is_valid == 1 && $user->telegram_user_id) {
                $inputFile = InputFile::create($publicPath, $file_name);

                Telegram::sendDocument([
                    'chat_id' => $user->telegram_user_id,
                    'document' => $inputFile,
                    'caption' => "Your calendar export is ready",
                    'parse_mode' => 'HTML',
                ]);
            }

            return response()->download($publicPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_export_clothes_calendar_daily_pdf(Request $request, $date){
        try {
            $user_id = $request->user()->id;
            $datetime = date('Y-m-d_H-i-s');
            $user = UserModel::getProfile($user_id);
            $file_name = "calendar-daily-$date-$user->username-$datetime.pdf";

            $res_used_history = ClothesUsedModel::getClothesUsedHistoryCalendar($user_id, null, null, $date);
            $res_wash_schedule = WashModel::getWashCalendar($user_id, null, null, $date);
            $res_weekly_schedule = ScheduleModel::getWeeklyScheduleCalendar($user_id, $date);
            $res_buyed_history = ClothesModel::getClothesBuyedCalendar($user_id, null, null, $date);
            $res_add_wardrobe = ClothesModel::getClothesCreatedCalendar($user_id, null, null, $date);

            $html = Document::documentDailyWeeklyReport(null,null,null,'daily',$date,$res_used_history,$res_wash_schedule,$res_weekly_schedule,$res_buyed_history,$res_add_wardrobe);
            $options = new DompdfOptions();
            $options->set('defaultFont', 'Helvetica');
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            if($user->telegram_user_id){
                $pdfContent = $dompdf->output();
                $pdfFilePath = public_path($file_name);
                file_put_contents($pdfFilePath, $pdfContent);
                $inputFile = InputFile::create($pdfFilePath, $pdfFilePath);

                Telegram::sendDocument([
                    'chat_id' => $user->telegram_user_id,
                    'document' => $inputFile,
                    'caption' => "Your daily report is ready",
                    'parse_mode' => 'HTML'
                ]);

                unlink($pdfFilePath);
            }

            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "inline; filename='$file_name'");
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_export_clothes_detail_by_id_pdf(Request $request, $id){
        try {
            $user_id = $request->user()->id;
            $datetime = date('Y-m-d_H-i-s');
            $user = UserModel::getProfile($user_id);

            $res_clothes = ClothesModel::select('*')
                ->where('id',$id)
                ->where('created_by',$user_id)
                ->first();

            if($res_clothes){
                $res_used = ClothesUsedModel::getClothesUsedHistory($id,$user_id);
                $res_wash = WashModel::getWashHistory($id,$user_id);
                $last_used = ClothesUsedModel::getLastUsed($user_id);
                $res_schedule = ScheduleModel::getScheduleByClothes($id, $user_id);
                $res_outfit = OutfitRelModel::getClothesFoundInOutfit($id,$user_id);
                $file_name = "clothes-detail-$res_clothes->clothes_name-$user->username-$datetime.pdf";

                $html = Document::documentClothesDetail(null,null,null,$res_clothes,$res_used,$res_wash,$last_used,$res_schedule,$res_outfit);
                $options = new DompdfOptions();
                $options->set('defaultFont', 'Helvetica');
                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                if($user->telegram_user_id){
                    $pdfContent = $dompdf->output();
                    $pdfFilePath = public_path($file_name);
                    file_put_contents($pdfFilePath, $pdfContent);
                    $inputFile = InputFile::create($pdfFilePath, $pdfFilePath);

                    Telegram::sendDocument([
                        'chat_id' => $user->telegram_user_id,
                        'document' => $inputFile,
                        'caption' => "Your clothes detail document is ready",
                        'parse_mode' => 'HTML'
                    ]);

                    unlink($pdfFilePath);
                }

                return response($dompdf->output(), 200)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', "inline; filename='$file_name'");
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "clothes"),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
