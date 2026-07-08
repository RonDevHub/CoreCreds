<?php
namespace CoreCreds\Generators;

use CoreCreds\Core\CSPRNG;

class PasswordGenerator 
{
    public static function generate(array $options): array 
    {
        $length = (int)($options['length'] ?? 16);
        if ($length < 8) $length = 8;
        if ($length > 64) $length = 64;

        $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower = 'abcdefghijkmnopqrstuvwxyz';
        $nums = '123456789';
        $syms = '!@#$%^&*()_+-=[]{};:,.<>?';

        if (!isset($options['exclude_similar']) || !$options['exclude_similar']) {
            $upper .= 'I';
            $lower .= 'lo';
            $nums .= '0';
            $syms .= 'O'; 
        }

        $pool = '';
        $required = [];

        if ($options['uppercase'] ?? false) {
            $pool .= $upper;
            $required[] = $upper[CSPRNG::getInt(0, strlen($upper) - 1)];
        }
        if ($options['lowercase'] ?? false) {
            $pool .= $lower;
            $required[] = $lower[CSPRNG::getInt(0, strlen($lower) - 1)];
        }
        if ($options['numbers'] ?? false) {
            $pool .= $nums;
            $required[] = $nums[CSPRNG::getInt(0, strlen($nums) - 1)];
        }
        if ($options['symbols'] ?? false) {
            $pool .= $syms;
            $required[] = $syms[CSPRNG::getInt(0, strlen($syms) - 1)];
        }

        if (empty($pool)) {
            $pool = $lower . $nums;
            $required[] = $lower[CSPRNG::getInt(0, strlen($lower) - 1)];
        }

        $password = '';
        foreach ($required as $char) {
            $password .= $char;
        }

        while (strlen($password) < $length) {
            $password .= $pool[CSPRNG::getInt(0, strlen($pool) - 1)];
        }

        $arr = str_split($password);
        $shuffled = '';
        while (count($arr) > 0) {
            $idx = CSPRNG::getInt(0, count($arr) - 1);
            $shuffled .= $arr[$idx];
            array_splice($arr, $idx, 1);
        }

        $entropy = $length * log(strlen($pool), 2);
        $strength = 0;
        if ($entropy > 40) $strength = 1;
        if ($entropy > 60) $strength = 2;
        if ($entropy > 80) $strength = 3;
        if ($entropy > 100) $strength = 4;

        return ['result' => $shuffled, 'strength' => $strength];
    }
}