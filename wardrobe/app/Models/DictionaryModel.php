<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Dictionary",
 *     type="object",
 *     required={"id", "dictionary_type", "dictionary_name"},
 *
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary key for the dictionary"),
 *     @OA\Property(property="dictionary_type", type="string", maxLength=36, description="Type or group of the dictionary"),
 *     @OA\Property(property="dictionary_name", type="string", maxLength=75, description="Unique name of the dictionary")
 * )
 */

class DictionaryModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'dictionary';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'dictionary_type', 'dictionary_name'];

    public static function isUsedName($name, $type){
        return DictionaryModel::whereRaw('LOWER(dictionary_name) = LOWER(?)', [$name])
            ->whereRaw('LOWER(dictionary_type) = LOWER(?)', [$type])
            ->exists();
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
