<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class JsonKeyPattern implements ValidationRule
{
    /**
     * Pola: snake_case atau dot.notation, huruf kecil/angka/underscore.
     * Contoh valid: code, amount, meta.gateway, voucher_code
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            return; // biarkan rule array/nullable lain yang urus
        }

        $pattern = '/^[a-z][a-z0-9_]*(\.[a-z0-9_]+)*$/';

        foreach (array_keys($value) as $key) {
            if ($key === '' || $key === null) {
                $fail('Kunci JSON tidak boleh kosong.');
                return;
            }
            if (! preg_match($pattern, $key)) {
                $fail("Format kunci '{$key}' tidak valid. Gunakan snake_case atau dot.notation (a-z, 0-9, _).");
                return;
            }
        }
    }
}
