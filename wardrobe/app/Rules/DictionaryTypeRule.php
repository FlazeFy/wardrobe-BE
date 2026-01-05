<?php

namespace App\Rules;
use Illuminate\Contracts\Validation\Rule;

class DictionaryTypeRule implements Rule
{
    public function passes($attribute, $value)
    {
        $type = ['clothes_category','clothes_gender','clothes_made_from','clothes_size','clothes_type','day_name','track_source','used_context','wash_type','weather_hit_from'];

        foreach ($type as $format) {
            if ($format === $value) {
                return true;
            }
        }

        return false;
    }

    public function message()
    {
        return 'Dictionary Type is not available';
    }
}