<?php

namespace GraphQL\Document\Parser\Language\SourceText;

/*
WhiteSpace ::
- "Horizontal Tab (U+0009)"
- "Space (U+0020)"
 */
function WhiteSpace($string)
{
    $char = substr($string, 0, 1);
    if ($char === "\u{0009}" || $char === "\u{0020}") {
        return [$char, substr($string, 1)];
    }

    return [null, $string];
}
