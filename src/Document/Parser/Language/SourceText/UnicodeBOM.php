<?php

namespace GraphQL\Document\Parser\Language\SourceText;

// UnicodeBOM :: "Byte Order Mark (U+FEFF)"
function UnicodeBOM($string)
{
    $char = substr($string, 0, 1);
    if ($char === "\u{FEFF}") {
        return [$char, substr($string, 1)];
    }

    return [null, $string];
}
