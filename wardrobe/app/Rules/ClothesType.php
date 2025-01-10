<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ClothesType implements Rule
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
        $type = ['hat', 'pants', 'shirt', 'jacket', 'shoes', 'socks', 'scarf', 'gloves', 'shorts', 'skirt', 'dress', 'blouse', 'sweater', 'hoodie', 'tie', 'belt', 
        'coat', 'underwear', 'swimsuit', 'vest', 't-shirt', 'jeans', 'leggings', 'boots', 'sandals', 'sneakers', 'raincoat', 'poncho', 'cardigan'];

        foreach ($type as $format) {
            if ($format === $value) {
                return true;
            }
        }

        return false;
    }

    public function message()
    {
        return 'Clothes Type is not available';
    }
}