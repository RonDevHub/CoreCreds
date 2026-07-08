<?php
namespace CoreCreds\Generators;

use CoreCreds\Core\CSPRNG;

class PassphraseGenerator 
{
    private static function countLines(string $filepath): int 
    {
        if (!file_exists($filepath)) return 0;
        $lineCount = 0;
        $handle = fopen($filepath, "r");
        if ($handle) {
            while (!feof($handle)) {
                fgets($handle);
                $lineCount++;
            }
            fclose($handle);
        }
        return $lineCount;
    }

    private static function getRandomLine(string $filepath, int $totalLines): string 
    {
        if ($totalLines <= 0) return '';
        $targetLine = CSPRNG::getInt(1, $totalLines);
        $handle = fopen($filepath, "r");
        $currentLine = 0;
        $lineText = '';
        if ($handle) {
            while (!feof($handle)) {
                $currentLine++;
                $txt = fgets($handle);
                if ($currentLine === $targetLine) {
                    $lineText = trim($txt);
                    break;
                }
            }
            fclose($handle);
        }
        
        $parts = preg_split('/\s+/', $lineText, 2);
        return isset($parts[1]) ? trim($parts[1]) : trim($lineText);
    }

    public static function generate(array $options): array 
    {
        $wordCount = (int)($options['word_count'] ?? 5);
        if ($wordCount < 3) $wordCount = 3;
        if ($wordCount > 12) $wordCount = 12;

        $listOpt = $options['wordlist'] ?? 'mix';
        $baseDir = __DIR__ . '/../../data/';
        $files = [];

        if ($listOpt === 'dice-de' || $listOpt === 'mix') $files['dice-de'] = $baseDir . 'dice-de.txt';
        if ($listOpt === 'dice-lat' || $listOpt === 'mix') $files['dice-lat'] = $baseDir . 'dice-lat.txt';
        if ($listOpt === 'eff' || $listOpt === 'mix') $files['eff'] = $baseDir . 'eff.txt';

        $fileCounts = [];
        foreach ($files as $k => $f) {
            if (file_exists($f)) {
                $fileCounts[$k] = self::countLines($f);
            }
        }

        if (empty($fileCounts)) {
            return ['result' => 'Error: No wordlists found.', 'strength' => 0];
        }

        $words = [];
        for ($i = 0; $i < $wordCount; $i++) {
            $chosenKey = CSPRNG::getRandomElement(array_keys($fileCounts));
            $words[] = self::getRandomLine($files[$chosenKey], $fileCounts[$chosenKey]);
        }

        $numCount = ($options['numbers'] ?? false) ? CSPRNG::getInt(1, max(1, (int)($wordCount / 2))) : 0;
        $symCount = ($options['symbols'] ?? false) ? CSPRNG::getInt(1, max(1, (int)($wordCount / 2))) : 0;

        $indices = range(0, $wordCount - 1);
        $numIndices = [];
        $symIndices = [];

        for ($i = 0; $i < $numCount; $i++) {
            if (empty($indices)) break;
            $idxKey = CSPRNG::getInt(0, count($indices) - 1);
            $numIndices[] = $indices[$idxKey];
            array_splice($indices, $idxKey, 1);
        }

        for ($i = 0; $i < $symCount; $i++) {
            if (empty($indices)) break;
            $idxKey = CSPRNG::getInt(0, count($indices) - 1);
            $symIndices[] = $indices[$idxKey];
            array_splice($indices, $idxKey, 1);
        }

        $symbolsPool = '!@#$%&()+=[]{}|;:,<>?';
        foreach ($words as $idx => &$w) {
            if ($options['word_start_upper'] ?? false) {
                $w = ucfirst($w);
            } elseif ($options['word_start_mix'] ?? false) {
                $w = (CSPRNG::getInt(0, 1) === 1) ? ucfirst($w) : lcfirst($w);
            }

            if (in_array($idx, $numIndices)) {
                $w .= CSPRNG::getInt(1, 9);
            } elseif (in_array($idx, $symIndices)) {
                $w .= $symbolsPool[CSPRNG::getInt(0, strlen($symbolsPool) - 1)];
            }
        }

        $separator = $options['separator'] ?? ' ';
        if ($separator === 'none') $separator = '';

        $resultStr = implode($separator, $words);

        $strength = 2;
        if ($wordCount >= 5) $strength = 3;
        if ($wordCount >= 8) $strength = 4;

        return ['result' => $resultStr, 'strength' => $strength];
    }
}