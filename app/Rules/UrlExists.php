<?php

namespace App\Rules;

use App\Rules\UrlExistsHelper;
use Illuminate\Contracts\Validation\Rule;

class UrlExists implements Rule
{
    private $helper;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(UrlExistsHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return $this->helper->isExists($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'This :attribute can’t be reached';
    }
}
