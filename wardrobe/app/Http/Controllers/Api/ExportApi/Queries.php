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

// Models
use App\Models\ClothesModel;
use App\Models\UserModel;
use App\Models\ClothesUsedModel;
use App\Models\WashModel;

// Helper
use App\Helpers\Generator;

class Queries extends Controller
{
    public function get_export_clothes_excel(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $datetime = date('Y-m-d H:i:s');
            $user = UserModel::getProfile($user_id);

            $active_clothes = ClothesModel::getClothesExport($user_id, 'active');
            $deleted_clothes = ClothesModel::getClothesExport($user_id, 'deleted');

            return Excel::download(new class($active_clothes, $deleted_clothes) implements WithMultipleSheets {
                private $active_clothes;
                private $deleted_clothes;

                public function __construct($active_clothes, $deleted_clothes)
                {
                    $this->activeClothes = $active_clothes;
                    $this->deletedClothes = $deleted_clothes;
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
            }, "clothes-$user->username-$datetime.xlsx");
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
            $datetime = date('Y-m-d H:i:s');
            $user = UserModel::getProfile($user_id);

            $clothes_used = ClothesUsedModel::getClothesUsedExport($user_id);

            return Excel::download(new class($clothes_used) implements WithMultipleSheets {
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
            }, "clothes_used-$user->username-$datetime.xlsx");
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
            $datetime = date('Y-m-d H:i:s');
            $user = UserModel::getProfile($user_id);

            $wash_history = WashModel::getWashExport($user_id);

            return Excel::download(new class($wash_history) implements WithMultipleSheets {
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
            }, "wash_history-$user->username-$datetime.xlsx");
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
