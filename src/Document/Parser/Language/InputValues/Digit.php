<?php

namespace GraphQL\Document\Parser\Language\InputValues;

// Digit :: one of 0 1 2 3 4 5 6 7 8 9
function Digit($string)
{
    $char = substr($string, 0, 1);
    if ($char === '0' ||
        $char === '1' ||
        $char === '2' ||
        $char === '3' ||
        $char === '4' ||
        $char === '5' ||
        $char === '6' ||
        $char === '7' ||
        $char === '8' ||
        $char === '9'
    ) {
        return [$char, substr($string, 1)];
    }

    return [null, $string];
}
