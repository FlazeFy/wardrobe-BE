<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClothesUsedModel extends Model
{
    use HasFactory;

    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'clothes_used';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'clothes_id', 'clothes_note', 'used_context', 'created_at', 'created_by'];

}
