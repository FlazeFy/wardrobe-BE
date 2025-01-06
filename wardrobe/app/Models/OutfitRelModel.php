<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutfitRelModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'outfit_relation';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'outfit_id', 'clothes_id', 'created_at', 'created_by'];
}
