<?php

namespace GraphQL\Document\Parser\Language\Fragments;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\TypeReferences\NamedType;
use function GraphQL\Document\Parser\util\Keyword;

// TypeCondition : on NamedType
function TypeCondition($string)
{
    [$on, $substr] = Keyword($string, 'on');
    if ($on !== 'on') {
        return [null, $string];
    }

    [$type, $substr] = NamedType($substr);
    if ($type === null) {
        Parser::throwSyntax('NamedType', $substr);
    }

    return [$type, $substr];
}
