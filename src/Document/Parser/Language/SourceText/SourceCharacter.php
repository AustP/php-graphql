<?php

namespace GraphQL\Document\Parser\Language\SourceText;

// SourceCharacter :: /[\u0009\u000A\u000D\u0020-\uFFFF]/
function SourceCharacter($string)
{
    $char = substr($string, 0, 1);
    $pattern = '~[\x{0009}\x{000A}\x{000D}\x{0020}-\x{FFFF}]~u';
    if (preg_match($pattern, $char) === 1) {
        return [$char, substr($string, 1)];
    }

    return [null, $string];
}
