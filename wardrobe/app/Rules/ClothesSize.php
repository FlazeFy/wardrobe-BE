<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ClothesSize implements Rule
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
        $type = ['S','M','L','XL','XXL','XXL'];

        foreach ($type as $format) {
            if ($format === $value) {
                return true;
            }
        }

        return false;
    }

    public function message()
    {
        return 'Clothes Size is not available';
    }
}