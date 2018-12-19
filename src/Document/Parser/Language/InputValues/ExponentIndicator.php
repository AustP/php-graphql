<?php

namespace GraphQL\Document\Parser\Language\InputValues;

// ExponentIndicator :: one of e E
function ExponentIndicator($string)
{
    $char = substr($string, 0, 1);
    if ($char === 'e' ||
        $char === 'E'
    ) {
        return [$char, substr($string, 1)];
    }

    return [null, $string];
}
