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
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;

// Models
use App\Models\ClothesModel;
use App\Models\UserModel;
use App\Models\ClothesUsedModel;
use App\Models\WashModel;
use App\Models\HistoryModel;

// Helper
use App\Helpers\Generator;

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

            $wash_history = WashModel::getWashExport($user_id);

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
}
