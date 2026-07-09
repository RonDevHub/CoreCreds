<?php
namespace CoreCreds\Generators;

use CoreCreds\Core\CSPRNG;

class UsernameGenerator 
{
    public static function generate(array $options): array 
    {
        $base = trim($options['base_name'] ?? '');
        $caseOpt = $options['username_case'] ?? 'default';
        
        if (empty($base)) {
            $baseDir = __DIR__ . '/../../data/';
            $candidateFiles = ['dice-de.txt', 'dice-la.txt', 'eff.txt'];
            $validFiles = [];
            foreach ($candidateFiles as $cf) {
                if (file_exists($baseDir . $cf)) {
                    $validFiles[] = $baseDir . $cf;
                }
            }

            if (!empty($validFiles)) {
                $f = CSPRNG::getRandomElement($validFiles);
                $lines = file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                if (!empty($lines)) {
                    $line = CSPRNG::getRandomElement($lines);
                    $parts = preg_split('/\s+/', $line, 2);
                    $base = isset($parts[1]) ? trim($parts[1]) : trim($line);
                }
            }
        }

        if (empty($base)) {
            $base = 'User';
        }

        $base = preg_replace('/[^A-Za-z0-9]/', '', $base);

        if ($caseOpt === 'upper') {
            $base = ucfirst(strtolower($base));
        } elseif ($caseOpt === 'lower') {
            $base = strtolower($base);
        }

        $digitCount = (int)($options['digit_count'] ?? 0);
        $placement = $options['placement'] ?? 'end';

        $digits = '';
        for ($i = 0; $i < $digitCount; $i++) {
            $digits .= CSPRNG::getInt(0, 9);
        }

        if ($placement === 'start') {
            $result = $digits . $base;
        } elseif ($placement === 'random' && $digitCount > 0) {
            $arr = str_split($base);
            for ($i = 0; $i < strlen($digits); $i++) {
                $pos = CSPRNG::getInt(0, count($arr));
                array_splice($arr, $pos, 0, $digits[$i]);
            }
            $result = implode('', $arr);
        } else {
            $result = $base . $digits;
        }

        return ['result' => $result, 'strength' => 3];
    }
}