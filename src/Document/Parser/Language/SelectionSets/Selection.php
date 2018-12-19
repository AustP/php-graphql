<?php

namespace GraphQL\Document\Parser\Language\SelectionSets;

use GraphQL\Document\Parser\Exceptions\SyntaxError;
use function GraphQL\Document\Parser\Language\Fields\Field;
use function GraphQL\Document\Parser\Language\Fragments\FragmentSpread;
use function GraphQL\Document\Parser\Language\Fragments\InlineFragment;

/*
Selection :
- Field
- FragmentSpread
- InlineFragment
 */
function Selection($string)
{
    [$field, $substr] = Field($string);
    if ($field !== null) {
        return [$field, $substr];
    }

    try {
        [$spread, $substr] = FragmentSpread($string);
        if ($spread !== null) {
            return [$spread, $substr];
        }

        [$fragment, $substr] = InlineFragment($string);
        if ($fragment !== null) {
            return [$fragment, $substr];
        }
    } catch (SyntaxError $e) {
        [$fragment, $substr] = InlineFragment($string);
        if ($fragment !== null) {
            return [$fragment, $substr];
        }

        [$spread, $substr] = FragmentSpread($string);
        if ($spread !== null) {
            return [$spread, $substr];
        }
    }

    return [null, $string];
}
