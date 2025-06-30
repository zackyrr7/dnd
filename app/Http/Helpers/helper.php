<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request; // gunakan facade

if (!function_exists('cekRM')) {
    function cekRM($room)
    {
        $token = Request::cookie('user_token');

        if (!$token) {
            return false;
        }

        $rm = DB::select("SELECT room FROM rooms WHERE user_token = ? and room = '$room'", [$token]);

        return !empty($rm) && $rm[0]->room !== null;
    }
}

//function token
if (!function_exists('token')) {
    function token()
    {
        $token = Request::cookie('user_token');
return $token;
    }
}
