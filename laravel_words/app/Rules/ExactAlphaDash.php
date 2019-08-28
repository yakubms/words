<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ExactAlphaDash implements Rule
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

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match('/[a-zA-Z-_.]/', $value) > 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '使用できるのは半角英数字と記号(-_.)のみです。';
    }
}
