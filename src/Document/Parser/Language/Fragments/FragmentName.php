<?php

namespace GraphQL\Document\Parser\Language\Fragments;

use function GraphQL\Document\Parser\Language\SourceText\Name;

// FragmentName : Name but not on
function FragmentName($string)
{
    [$name, $substr] = Name($string);
    if ($name === null || $name === 'on') {
        return [null, $string];
    }

    return [$name, $substr];
}
