<?php

use App\Models\Setting;

if (!function_exists('setting')) {

    function setting()
    {
        return Setting::first();
    }

}

if (!function_exists('format_qty')) {
    function format_qty($value): string
    {
        $formatted = number_format((float) $value, 2, ',', '');
        $formatted = rtrim(rtrim($formatted, '0'), ',');

        return $formatted === '' ? '0' : $formatted;
    }
}
