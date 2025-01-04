<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MostUsedContextColumn implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function passes($attribute, $value)
    {
        $type = ['clothes_merk','clothes_size','clothes_gender','clothes_made_from','clothes_category','clothes_type'];

        foreach ($type as $format) {
            if ($format === $value) {
                return true;
            }
        }

        return false;
    }

    public function message()
    {
        return 'Context is not available';
    }
}