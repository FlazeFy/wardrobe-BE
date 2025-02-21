<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CalendarClothesExport implements FromCollection, WithHeadings, WithTitle
{
    private $clothes;

    public function __construct($clothes,$sheet_title)
    {
        $this->inventory = $clothes;
        $this->sheet_title = $sheet_title;
    }
    public function collection()
    {
        return $this->inventory;
    }
    public function headings(): array
    {
        return ['date','clothes_name', 'clothes_type'];
    }
    public function title(): string
    {
        return $this->sheet_title;
    }
}
