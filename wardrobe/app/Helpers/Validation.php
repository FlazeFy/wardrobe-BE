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
use App\Rules\UsedContext;
use App\Rules\DayName;
use App\Rules\WashType;

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
        } else if($type == 'create_outfit_relation'){
            return Validator::make($request->all(), [
                'clothes_name' => 'required|string|max:75|min:2',
                'clothes_type' => ['required', new ClothesType],
                'clothes_id' => 'required|string|max:36',
            ]); 
        }
    }

    public static function getValidateWash($request){
        if($type == 'create'){
            return Validator::make($request->all(), [
                'wash_note' => 'nullable|string|max:75|min:2', 
                'clothes_id' => 'required|string|max:36|min:36', 
                'wash_type' => ['required', new WashType],
                'wash_checkpoint' => ['nullable', 'array'], 
                'wash_checkpoint.*.id' => ['required_with:wash_checkpoint', 'integer'], 
                'wash_checkpoint.*.checkpoint_name' => ['required_with:wash_checkpoint', 'string'], 
                'wash_checkpoint.*.is_finished' => ['required_with:wash_checkpoint', 'boolean'], 
            ]); 
        } 
    }

    public static function getValidateClothesUsed($request,$type){
        if($type == 'create'){
            return Validator::make($request->all(), [
                'clothes_note' => 'nullable|string|max:144',
                'used_context' => ['required', new UsedContext],
            ]); 
        } 
    }

    public static function getValidateSchedule($request,$type){
        if($type == 'create'){
            return Validator::make($request->all(), [
                'is_remind' => 'required|boolean',
                'schedule_note' => 'nullable|string|max:255',
                'day' => ['required', new DayName],
            ]); 
        } 
    }

    public static function getValidateFeedback($request){
        return Validator::make($request->all(), [
            'feedback_rate' => 'required|numeric|max:5',
            'feedback_body' => 'required|string|max:144',
        ]); 
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
