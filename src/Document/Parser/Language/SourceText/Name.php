<?php

namespace GraphQL\Document\Parser\Language\SourceText;

use function GraphQL\Document\Parser\util\LexicalToken;

// Name :: /[_A-Za-z][_0-9A-Za-z]*/
function Name($string)
{
    return LexicalToken($string, function ($string) {
        $matches = [];
        $pattern = '~^[_A-Za-z][_0-9A-Za-z]*~';
        if (preg_match($pattern, $string, $matches) !== 1) {
            return [null, $string];
        }

        return [$matches[0], substr($string, strlen($matches[0]))];
    });
}
