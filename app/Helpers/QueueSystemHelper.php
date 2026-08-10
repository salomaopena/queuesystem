<?php

if (!function_exists('showValidationErrors')) {
    function showValidationErrors($fieldName, $validationErrors)
    {
        if ($validationErrors->has($fieldName)) {
            return '<div class="text-red-500 text-sm mt-1 italic">'
                . $validationErrors->first($fieldName) . '</div>';
        } else {
            return '';
        }
    }
}


if (!function_exists('showServerError')) {
    function showServerError()
    {
        if (session()->has('server_error')) {
            return '<div class="text-red-500 text-sm mt-1 italic">'
                . session()->get('server_error') . '</div>';
        } else {
            return '';
        }
    }
}