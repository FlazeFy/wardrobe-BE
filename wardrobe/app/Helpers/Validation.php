<?php

namespace App\Helpers;
use Illuminate\Support\Facades\Validator;

// Rule
use App\Rules\DictionaryTypeRule;
use App\Rules\MostUsedContextColumnRule;
use App\Rules\YearlyContextColumnRule;
use App\Rules\ClothesSizeRule;
use App\Rules\ClothesGenderRule;
use App\Rules\ClothesMadeFromRule;
use App\Rules\ClothesCategoryRule;
use App\Rules\ClothesTypeRule;
use App\Rules\UsedContextRule;
use App\Rules\DayNameRule;
use App\Rules\WashTypeRule;

class Validation
{
    public static function getValidateLogin($request){
        return Validator::make($request->all(), [
            'username' => 'required|min:6|max:30|string',
            'password' => 'required|min:6|string'
        ]);
    }

    public static function getValidateRegisterToken($request){
        return Validator::make($request->all(), [
            'username' => 'required|min:6|max:30|string',
            'token' => 'required|min:6|max:6|string',
        ]);
    }

    public static function getValidateRegister($request){
        return Validator::make($request->all(), [
            'username' => 'required|min:6|max:30|string',
            'password' => 'required|min:6|string',
            'email' => 'required|min:10|max:255|email|string'
        ]);
    }

    public static function getValidateUser($request, $type){
        if($type == "update_fcm"){
            return Validator::make($request->all(), [
                'firebase_fcm_token' => 'nullable|min:10|max:255|string',
            ]);
        }
    }

    public static function getValidateDictionary($request,$type){
        if($type == 'create'){
            return Validator::make($request->all(), [
                'dictionary_name' => 'required|string|max:75|min:2',
                'dictionary_type' => ['required', new DictionaryTypeRule],
            ]);  
        } else if($type == 'delete'){
            return Validator::make($request->all(), [
                'id' => 'required|string|max:36',
            ]); 
        }
    }

    public static function getValidateQuestion($request,$type){
        if($type == 'create'){
            return Validator::make($request->all(), [
                'question' => 'required|string|max:500|min:2',
            ]);  
        } 
    }

    public static function getValidateClothes($request,$type){
        if($type == 'create'){
            return Validator::make($request->all(), [
                'clothes_name' => 'required|string|max:75|min:2',
                'clothes_desc' => 'nullable|string|max:500',
                'clothes_merk' => 'nullable|string|max:75',
                'clothes_size' => ['required', new ClothesSizeRule],
                'clothes_gender' => ['required', new ClothesGenderRule],
                'clothes_made_from' => ['required', new ClothesMadeFromRule],
                'clothes_color' => 'required|string|max:36',
                'clothes_category' => ['required', new ClothesCategoryRule],
                'clothes_type' => ['required', new ClothesTypeRule],
                'clothes_price' => 'required|integer|min:0|max:999999999',
                'clothes_buy_at' => 'nullable|date_format:Y-m-d',
                'clothes_qty' => 'required|integer|min:0|max:999',
                'is_faded' => 'required|boolean',
                'has_washed' => 'required|boolean',
                'has_ironed' => 'required|boolean',
                'is_favorite' => 'required|boolean',
            ]); 
        } else if($type == 'create_outfit_relation'){
            return Validator::make($request->all(), [
                'clothes_name' => 'required|string|max:75|min:2',
                'clothes_type' => ['required', new ClothesTypeRule],
                'clothes_id' => 'required|string|min:36|max:36',
            ]); 
        }
    }

    public static function getValidateWash($request,$type){
        if($type == 'create'){
            return Validator::make($request->all(), [
                'wash_note' => 'nullable|string|max:75|min:2', 
                'clothes_id' => 'required|string|max:36|min:36', 
                'wash_type' => ['required', new WashTypeRule],
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
                'used_context' => ['required', new UsedContextRule],
            ]); 
        } 
    }

    public static function getValidateSchedule($request,$type){
        if($type == 'create'){
            return Validator::make($request->all(), [
                'is_remind' => 'required|boolean',
                'schedule_note' => 'nullable|string|max:255',
                'day' => ['required', new DayNameRule],
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
                'context' => ['required', new MostUsedContextColumnRule],
            ]);  
        } else if($type == 'yearly_context'){
            return Validator::make($request->all(), [
                'context' => ['required', new YearlyContextColumnRule],
            ]); 
        }
    }

    public static function hasNumber($val) {
        $pattern = '/\d/';
      
        return preg_match($pattern, $val);
    }
}
