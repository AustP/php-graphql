<?php

namespace GraphQL\Document\Parser\Language\Fields;

use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;

// Alias : Name :
function Alias($string)
{
    [$name, $substr] = Name($string);
    if ($name === null) {
        return [null, $string];
    }

    [$colon, $substr] = Punctuator($substr);
    if ($colon !== ':') {
        return [null, $string];
    }

    return [$name, $substr];
}
