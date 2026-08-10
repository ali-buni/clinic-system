<?php

namespace App\Support;

class TextSanitizer
{
    public static function html(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
