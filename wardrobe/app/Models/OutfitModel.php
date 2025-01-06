<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutfitModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'outfit';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'outfit_name', 'outfit_note', 'is_favorite', 'is_auto', 'created_at', 'created_by', 'updated_at'];
}
