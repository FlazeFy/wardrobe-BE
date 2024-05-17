<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WashModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'wash';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'wash_note', 'clothes_id', 'wash_type', 'wash_checkpoint', 'created_at', 'created_by', 'finished_at'];

    protected $casts = [
        'wash_checkpoint' => 'array'
    ];
}
