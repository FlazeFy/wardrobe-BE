<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UsedContext implements Rule
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
        $type = ['Worship','Shopping','Work','School','Campus','Sport','Party'];

        foreach ($type as $format) {
            if ($format === $value) {
                return true;
            }
        }

        return false;
    }

    public function message()
    {
        return 'Used Context is not available';
    }
}