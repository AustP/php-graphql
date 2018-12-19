<?php

namespace GraphQL\Document\Parser\TypeSystem\Directives;

use function GraphQL\Document\Parser\util\Keyword;

// TypeSystemDirectiveLocation : one of SCHEMA SCALAR OBJECT FIELD_DEFINITION
//   ARGUMENT_DEFINITION INTERFACE UNION ENUM ENUM_VALUE INPUT_OBJECT
//   INPUT_FIELD_DEFINITION
function TypeSystemDirectiveLocation($string)
{
    $keywords = [
        'SCHEMA',
        'SCALAR',
        'OBJECT',
        'FIELD_DEFINITION',
        'ARGUMENT_DEFINITION',
        'INTERFACE',
        'UNION',
        'ENUM',
        'ENUM_VALUE',
        'INPUT_OBJECT',
        'INPUT_FIELD_DEFINITION',
        'VARIABLE_DEFINITION'
    ];

    // NOTE: the documentation says VARIABLE_DEFINITION belongs in
    // ExecutableDirectiveLocation, but I'm pretty sure it belongs here

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
