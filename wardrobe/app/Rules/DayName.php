<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DayName implements Rule
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
        $type = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

        foreach ($type as $format) {
            if ($format === $value) {
                return true;
            }
        }

        return false;
    }

    public function message()
    {
        return 'Day is not available';
    }
}