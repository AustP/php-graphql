<?php

namespace GraphQL\Document\Parser\TypeSystem\Descriptions;

use function GraphQL\Document\Parser\Language\InputValues\StringValue;

// Description : StringValue
function Description($string)
{
    [$value, $substr] = StringValue($string);
    if ($value === null) {
        return [null, $string];
    }

    return [$value['value'], $substr];
}
