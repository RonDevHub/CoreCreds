<?php
namespace CoreCreds\Localization;

class Translator 
{
    private array $dict;
    private string $lang;

    public function __construct() 
    {
        $this->dict = require __DIR__ . '/../../config/languages.php';
        $this->lang = 'de';

        $accepted = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if (!empty($accepted)) {
            $primary = substr($accepted, 0, 2);
            if (isset($this->dict[$primary])) {
                $this->lang = $primary;
            }
        }
    }

    public function getLanguage(): string 
    {
        return $this->lang;
    }

    public function translate(string $key): string 
    {
        return $this->dict[$this->lang][$key] ?? $key;
    }

    public function getAll(): array 
    {
        return $this->dict[$this->lang] ?? [];
    }
}