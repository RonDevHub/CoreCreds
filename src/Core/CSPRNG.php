<?php
namespace CoreCreds\Core;

class CSPRNG 
{
    public static function getInt(int $min, int $max): int 
    {
        return random_int($min, $max);
    }

    public static function getRandomElement(array $array) 
    {
        if (empty($array)) {
            return null;
        }
        $index = self::getInt(0, count($array) - 1);
        return $array[$index];
    }
}