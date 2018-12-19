<?php

namespace GraphQL\Document\Parser\TypeSystem\Directives;

use function GraphQL\Document\Parser\util\Keyword;

// ExecutableDirectiveLocation : one of QUERY MUTATION SUBSCRIPTION FIELD
//   FRAGMENT_DEFINITION FRAGMENT_SPREAD INLINE_FRAGMENT VARIABLE_DEFINITION
function ExecutableDirectiveLocation($string)
{
    $keywords = [
        'QUERY',
        'MUTATION',
        'SUBSCRIPTION',
        'FIELD',
        'FRAGMENT_DEFINITION',
        'FRAGMENT_SPREAD',
        'INLINE_FRAGMENT'
    ];

    // NOTE: the documentation says VARIABLE_DEFINITION belongs in this list,
    // but I'm pretty sure it belongs in TypeSystemDirectiveLocation

    $bestMatch = '';
    $bestSubstr = '';
    foreach ($keywords as $keyword) {
        [$match, $nextSubstr] = Keyword($string, $keyword);
        if ($match === $keyword) {
            if (strlen($match) > strlen($bestMatch)) {
                $bestMatch = $match;
                $bestSubstr = $nextSubstr;
            }
        }
    }

    if ($bestMatch !== '') {
        return [$bestMatch, $bestSubstr];
    }

    return [null, $string];
}
