<?php

namespace GraphQL\Document\Parser\TypeSystem\Directives;

/*
DirectiveLocation :
- ExecutableDirectiveLocation
- TypeSystemDirectiveLocation
 */
function DirectiveLocation($string)
{
    $bestLocation = '';
    $bestSubstr = '';

    [$location, $nextSubstr] = ExecutableDirectiveLocation($string);
    if ($location !== null) {
        if (strlen($location) > strlen($bestLocation)) {
            $bestLocation = $location;
            $bestSubstr = $nextSubstr;
        }
    }

    [$location, $nextSubstr] = TypeSystemDirectiveLocation($string);
    if ($location !== null) {
        if (strlen($location) > strlen($bestLocation)) {
            $bestLocation = $location;
            $bestSubstr = $nextSubstr;
        }
    }

    if ($bestLocation !== '') {
        return [$bestLocation, $bestSubstr];
    }

    return [null, $string];
}
