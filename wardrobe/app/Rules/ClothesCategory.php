<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ClothesCategory implements Rule
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
        $type = ['upper_body','bottom_body','head','foot','hand'];

        foreach ($type as $format) {
            if ($format === $value) {
                return true;
            }
        }

        return false;
    }

    public function message()
    {
        return 'Clothes Category is not available';
    }
}