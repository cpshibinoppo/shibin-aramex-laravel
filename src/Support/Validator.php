<?php

namespace Shibin\Aramex\Support;

use Illuminate\Support\Facades\Validator as LaravelValidator;
use Shibin\Aramex\Exceptions\AramexException;

class Validator
{
    /**
     * Validate input data using Laravel's Validator
     *
     * @throws AramexException
     */
    public static function validate(array $data, array $rules): void
    {
        $validator = LaravelValidator::make($data, $rules);

        if ($validator->fails()) {
            throw new AramexException(
                "Validation failed",
                $validator->errors()->toArray()
            );
        }
    }
}
