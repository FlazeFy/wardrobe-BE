<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Validator;

// Rules
use App\Rules\DictionaryType;
use App\Rules\MostUsedContextColumn;
use App\Rules\YearlyContextColumn;

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
