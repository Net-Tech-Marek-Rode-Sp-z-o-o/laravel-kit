<?php

declare(strict_types=1);

namespace NetCode\Kit\Validation;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Symfony\Component\Intl\Countries;

final class CountryCode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! Countries::exists(strtoupper($value))) {
            $fail('The :attribute must be a valid ISO 3166-1 alpha-2 country code.');
        }
    }
}
