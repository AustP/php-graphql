<?php

namespace GraphQL\Document\Parser\Language\InputValues;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\SourceText\Name;

// EnumValue : Name but not true, false or null
function EnumValue($string)
{
    [$name, $substr] = Name($string);
    if ($name === null ||
        $name === 'true' ||
        $name === 'false' ||
        $name === 'null'
    ) {
        return [null, $string];
    }

    return [['type' => 'Enum', 'value' => $name], $substr];
}
