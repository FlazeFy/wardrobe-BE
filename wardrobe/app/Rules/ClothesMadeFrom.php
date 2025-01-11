<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ClothesMadeFrom implements Rule
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
        $type = ['cotton','wool','silk','linen','polyester','denim','leather','nylon','rayon','synthetic','cloth'];

        foreach ($type as $format) {
            if ($format === $value) {
                return true;
            }
        }

        return false;
    }

    public function message()
    {
        return 'Clothes Made From is not available';
    }
}