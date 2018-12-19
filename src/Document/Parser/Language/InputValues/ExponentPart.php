<?php

namespace GraphQL\Document\Parser\Language\InputValues;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\util\getRepeating;

// ExponentPart :: ExponentIndicator Sign? Digit+
function ExponentPart($string)
{
    [$indicator, $substr] = ExponentIndicator($string);
    if ($indicator === null) {
        return [null, $string];
    }

    [$sign, $substr] = Sign($substr);
    [$digits, $substr] = getRepeating($substr, function ($digits, $substr) {
        [$digit, $substr] = Digit($substr);
        if ($digit === null) {
            return null;
        }

        return [$digits . $digit, $substr];
    }, '', 1);
    if ($digits === null) {
        Parser::throwSyntax('Digit', $substr);
    }

    return [$indicator . ($sign ?? '') . $digits, $substr];
}
