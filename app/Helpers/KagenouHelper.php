<?php

use Illuminate\Support\Str;

if (!function_exists('generateUniqueID')) {
    function generateUniqueID($model, $column, $length = 8)
    {
        do {
            $uniqueID = Str::random($length);
        } while ($model::where($column, $uniqueID)->exists());

        return $uniqueID;
    }
}

if (!function_exists('formatNumber')) {
    function formatNumber($number, $decimals = 0, $decimalSeparator = '.', $thousandSeparator = ',')
    {
        return number_format($number, $decimals, $decimalSeparator, $thousandSeparator);
    }
}
