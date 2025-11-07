<?php
declare(strict_types=1);

namespace App\Util;

final class Validator
{
    public static function require(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            if (str_contains($rule, 'required') && ($value === null || $value === '')) {
                $errors[$field][] = 'The field is required.';
                continue;
            }
            if ($value !== null && str_contains($rule, 'string') && !is_string($value)) {
                $errors[$field][] = 'Must be a string.';
            }
        }
        return $errors;
    }
}
