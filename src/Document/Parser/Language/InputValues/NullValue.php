<?php

namespace GraphQL\Document\Parser\Language\InputValues;

use function GraphQL\Document\Parser\util\Keyword;

// NullValue : null
function NullValue($string)
{
    [$null, $substr] = Keyword($string, 'null');
    if ($null !== 'null') {
        return [null, $string];
    }

    return [['type' => 'Null', 'value' => null], $substr];
}
