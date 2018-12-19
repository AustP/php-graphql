<?php

namespace GraphQL\Document\Parser\Language\SourceText;

/*
LineTerminator ::
- "New Line (U+000A)"
- "Carriage Return (U+000D)" [ lookahead ! "New Line (U+000A)" ]
- "Carriage Return (U+000D)" "New Line (U+000A)"
 */
function LineTerminator($string)
{
    $char = substr($string, 0, 1);
    if ($char === "\u{000A}") {
        return [$char, substr($string, 1)];
    }

    if ($char === "\u{000D}") {
        $nextChar = substr($string, 1, 1);
        if ($nextChar === "\u{000A}") {
            return [$char . $nextChar, substr($string, 2)];
        }

        return [$char, substr($string, 1)];
    }

    return [null, $string];
}
