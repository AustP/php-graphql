<?php

namespace GraphQL\Document\Parser\Language\InputValues;

use function GraphQL\Document\Parser\util\Keyword;

// BooleanValue : one of true false
function BooleanValue($string)
{
    [$true, $substr] = Keyword($string, 'true');
    if ($true === 'true') {
        return [['type' => 'Boolean', 'value' => true], $substr];
    }

    [$false, $substr] = Keyword($string, 'false');
    if ($false === 'false') {
        return [['type' => 'Boolean', 'value' => false], $substr];
    }

    return [null, $string];
}
