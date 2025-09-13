<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DictionaryModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'dictionary';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'dictionary_type', 'dictionary_name'];

    public static function isUsedName($name, $type){
        $res = DictionaryModel::selectRaw('1')
            ->whereRaw('LOWER(dictionary_name) = LOWER(?)', [$name])
            ->whereRaw('LOWER(dictionary_type) = LOWER(?)', [$type])
            ->first();

        return $res ? true : false;
    }

    public static function getRandom($null,$type){
        if($null == 0){
            $data = DictionaryModel::inRandomOrder()->take(1)->where('dictionary_type',$type)->first();
            $res = $data->dictionary_name;
        } else {
            $res = null;
        }
        
        return $res;
    }
}
