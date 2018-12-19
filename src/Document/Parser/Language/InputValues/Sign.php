<?php

namespace GraphQL\Document\Parser\Language\InputValues;

// Sign :: one of + -
function Sign($string)
{
    $char = substr($string, 0, 1);
    if ($char === '+' ||
        $char === '-'
    ) {
        return [$char, substr($string, 1)];
    }

    return [null, $string];
}
