<?php

namespace GraphQL\Document\Parser\Language\InputValues;

// NegativeSign :: -
function NegativeSign($string)
{
    $char = substr($string, 0, 1);
    if ($char === '-') {
        return [$char, substr($string, 1)];
    }

    return [null, $string];
}
