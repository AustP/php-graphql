<?php

namespace GraphQL\Document\Parser\Language\Document;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Fragments\FragmentDefinition;
use function GraphQL\Document\Parser\Language\Operations\OperationDefinition;

/*
ExecutableDefinition :
- OperationDefinition
- FragmentDefinition
 */
function ExecutableDefinition($string)
{
    [$definition, $substr] = OperationDefinition($string);
    if ($definition !== null) {
        return [$definition, $substr];
    }

    [$definition, $substr] = FragmentDefinition($string);
    if ($definition !== null) {
        return [$definition, $substr];
    }

    return [null, $string];
}
