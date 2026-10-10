<?php

namespace App\Components;

/**
 * Class to generate and check random looking ids from real ids
 * @package App\Components
 */

class Uid
{
    public static function encode($number): string
    {
        $base32 = base_convert($number, 10, 32);
        return $base32 . mb_substr(md5($base32), -4);
    }

    public static function decode($uid): string|false
    {
        $base32 = mb_substr($uid, 0, -4);
        $number = base_convert($base32, 32, 10);
        if (self::encode($number) == $uid) {
            return $number;
        }
        return false;
    }
}
