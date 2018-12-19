<?php

namespace GraphQL\Document\Parser\Language\TypeReferences;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\SourceText\Name;

// NamedType : Name
function NamedType($string)
{
    [$name, $substr] = Name($string);
    if ($name === null) {
        return [null, $string];
    }

    return [['nullable' => true, 'value' => $name], $substr];
}
