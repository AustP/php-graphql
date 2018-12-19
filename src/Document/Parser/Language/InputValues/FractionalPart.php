<?php

namespace GraphQL\Document\Parser\Language\InputValues;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\util\getRepeating;

// FractionalPart :: . Digit+
function FractionalPart($string)
{
    $dot = $string[0] ?? '';
    if ($dot !== '.') {
        return [null, $string];
    }

    [$digits, $substr] = getRepeating(
        substr($string, 1),
        function ($digits, $substr) {
            [$digit, $substr] = Digit($substr);
            if ($digit === null) {
                return null;
            }

            return [$digits . $digit, $substr];
        },
        '',
        1
    );
    if ($digits === null) {
        Parser::throwSyntax('Digit', $substr);
    }

    return ['.' . $digits, $substr];
}
