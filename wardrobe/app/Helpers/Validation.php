<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Validator;

// Rules
use App\Rules\DictionaryType;
use App\Rules\MostUsedContextColumn;
use App\Rules\YearlyContextColumn;
use App\Rules\ClothesSize;
use App\Rules\ClothesGender;
use App\Rules\ClothesMadeFrom;
use App\Rules\ClothesCategory;
use App\Rules\ClothesType;

class Validation
{
    public static function getValidateLogin($request){
        return Validator::make($request->all(), [
            'username' => 'required|min:6|max:30|string',
            'password' => 'required|min:6|string'
        ]);
    }

    public static function getValidateDictionary($request,$type){
        if($type == 'create'){
            return Validator::make($request->all(), [
                'dictionary_name' => 'required|string|max:75|min:2',
                'dictionary_type' => ['required', new DictionaryType],
            ]);  
        } else if($type == 'delete'){
            return Validator::make($request->all(), [
                'id' => 'required|string|max:36',
            ]); 
        }
    }

    public static function getValidateClothes($request,$type){
        if($type == 'create'){
            return Validator::make($request->all(), [
                'clothes_name' => 'required|string|max:75|min:2',
                'clothes_desc' => 'nullable|string|max:500',
                'clothes_merk' => 'nullable|string|max:75',
                'clothes_size' => ['required', new ClothesSize],
                'clothes_gender' => ['required', new ClothesGender],
                'clothes_made_from' => ['required', new ClothesMadeFrom],
                'clothes_color' => 'required|string|max:36',
                'clothes_category' => ['required', new ClothesCategory],
                'clothes_type' => ['required', new ClothesType],
                'clothes_price' => 'required|integer|min:0',
                'clothes_buy_at' => 'nullable|date',
                'clothes_qty' => 'required|integer|min:0|max:999',
                'is_faded' => 'required|boolean',
                'has_washed' => 'required|boolean',
                'has_ironed' => 'required|boolean',
                'is_favorite' => 'required|boolean',
            ]); 
        } 
    }

    public static function getValidateStats($request,$type){
        if($type == 'most_context'){
            return Validator::make($request->all(), [
                'context' => ['required', new MostUsedContextColumn],
            ]);  
        } else if($type == 'yearly_context'){
            return Validator::make($request->all(), [
                'context' => ['required', new YearlyContextColumn],
            ]); 
        }
    }
}
