<?php

namespace GraphQL\Document\Parser\Language\InputValues;

use function GraphQL\Document\Parser\util\getRepeating;

/*
IntegerPart ::
- NegativeSign? 0
- NegativeSign? NonZeroDigit Digit*
 */
function IntegerPart($string)
{
    [$negative, $substr] = NegativeSign($string);
    $char = substr($substr, 0, 1);
    if ($char === '0') {
        return [0, substr($substr, 1)];
    }

    [$nonZero, $substr] = NonZeroDigit($substr);
    if ($nonZero === null) {
        return [null, $string];
    }

    [$digits, $substr] = getRepeating($substr, function ($digits, $substr) {
        [$digit, $substr] = Digit($substr);
        if ($digit === null) {
            return null;
        }

        return [$digits . $digit, $substr];
    }, '');

    return [(int)(($negative ?? '') . $nonZero . $digits), $substr];
}
