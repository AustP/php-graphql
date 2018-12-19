<?php

namespace GraphQL\Document\Parser\Language\InputValues;

// EscapedUnicode :: /[0-9A-Fa-f]{4}/
function EscapedUnicode($string)
{
    $matches = [];
    $pattern = '~^[0-9A-Fa-f]{4}~';
    if (preg_match($pattern, $string, $matches) !== 1) {
        return [null, $string];
    }

    return [$matches[0], substr($string, 4)];
}
