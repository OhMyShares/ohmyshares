<?php
namespace App\Components;

class ArrayUtils
{
    /**
     * Add [$key => $value] to each item in $array
     * @param $array
     * @param $key
     * @param $value
     */
    public static function addToAll(&$array, $key, $value): void
    {
        foreach ($array as &$item) {
            $item[$key] = $value;
        }
    }

    /**
     * Add value to array item (if item not existed it will be created)
     * @param $array
     * @param $key
     * @param $value
     */
    public static function addToItem(array &$array, $key, $value): array
    {
        if (isset($array[$key])) {
            $array[$key] += $value;
        } else {
            $array[$key] = $value;
        }
        return $array;
    }


    /**
     * Remove everything exclude allowed keys
     * @param $arr
     * @param $keys
     */
    public static function allowKeys(&$arr, $keys): void
    {
        if ($arr && is_array($arr)) {
            foreach ($arr as $key => &$value) {
                if (!in_array($key, $keys)) {
                    unset($arr[$key]);
                }
            }
        }
    }
}
