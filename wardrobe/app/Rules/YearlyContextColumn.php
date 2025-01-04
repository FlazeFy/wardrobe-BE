<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class YearlyContextColumn implements Rule
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
        $type = ["clothes_buy_at", "clothes_created_at", "wash_created_at", "clothes_used"];

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