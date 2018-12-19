<?php

namespace GraphQL\Document\Parser\Language\InputValues;

use function GraphQL\Document\Parser\Language\SourceText\SourceCharacter;

/*
BlockStringCharacter ::
- SourceCharacter but not """ or \"""
- \"""
 */
function BlockStringCharacter($string)
{
    [$char, $substr] = SourceCharacter($string);
    if ($char === '\\') {
        $chars = substr($substr, 0, 3);
        if ($chars === '"""') {
            return ['"""', substr($substr, 3)];
        }
    }

    if ($char === '"') {
        $chars = substr($substr, 0, 2);
        if ($chars === '""') {
            return [null, $string];
        }
    }

    if ($char !== null) {
        return [$char, $substr];
    }

    return [null, $string];
}
