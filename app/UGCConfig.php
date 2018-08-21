<?php
namespace App;

class UGCConfig
{
    const APP_NAME = "Letro";

    public static function get($path)
    {
        return \Config::get(app()->environment() . '.' . $path);
    }
}
