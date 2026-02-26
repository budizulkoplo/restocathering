<?php

use App\Models\Setting;

if (!function_exists('setting')) {

    function setting()
    {
        return Setting::first();
    }

}