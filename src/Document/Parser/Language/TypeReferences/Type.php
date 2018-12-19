<?php

namespace GraphQL\Document\Parser\Language\TypeReferences;

/*
Type :
- NamedType
- ListType
- NonNullType
 */
function Type($string)
{
    [$type, $substr] = NonNullType($string);
    if ($type !== null) {
        return [$type, $substr];
    }

    [$type, $substr] = NamedType($string);
    if ($type !== null) {
        return [$type, $substr];
    }

    [$list, $substr] = ListType($string);
    if ($list !== null) {
        return [$list, $substr];
    }

    return [null, $string];
}
