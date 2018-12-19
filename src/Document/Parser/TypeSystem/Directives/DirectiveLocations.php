<?php

namespace GraphQL\Document\Parser\TypeSystem\Directives;

use function GraphQL\Document\Parser\Language\SourceText\Punctuator;
use function GraphQL\Document\Parser\util\getRepeating;

/*
DirectiveLocations :
- |? DirectiveLocation
- DirectiveLocations | DirectiveLocation
 */
function DirectiveLocations($string)
{
    [$locations, $substr] = getRepeating(
        $string,
        function ($locations, $substr) {
            [$pipe, $substr] = Punctuator($substr);
            if (count($locations) >= 1 && $pipe !== '|') {
                return null;
            }

            [$location, $substr] = DirectiveLocation($substr);
            if ($location === null) {
                return null;
            }

            $locations[] = $location;
            return [$locations, $substr];
        },
        [],
        1
    );
    if ($locations === null) {
        return [null, $string];
    }

    return [$locations, $substr];
}
