<?php

namespace GraphQL\Document\Parser\Language\InputValues;

// EscapedCharacter :: one of " \ / b f n r t
function EscapedCharacter($string)
{
    $char = substr($string, 0, 1);
    if ($char === '"' ||
        $char === '\\' ||
        $char === '/' ||
        $char === 'b' ||
        $char === 'f' ||
        $char === 'n' ||
        $char === 'r' ||
        $char === 't'
    ) {
        return [$char, substr($string, 1)];
    }

    return [null, $string];
}
