<?php

namespace GraphQL\Document\Parser\Language\InputValues;

// NonZeroDigit :: Digit but not 0
function NonZeroDigit($string)
{
    [$digit, $substr] = Digit($string);
    if ($digit !== null && $digit !== '0') {
        return [$digit, $substr];
    }

    return [null, $string];
}
