<?php

namespace GraphQL\Document\Parser\Language\TypeReferences;

use function GraphQL\Document\Parser\Language\SourceText\Punctuator;

/*
NonNullType :
- NamedType !
- ListType !
 */
function NonNullType($string)
{
    [$type, $substr] = NamedType($string);
    if ($type !== null) {
        [$bang, $substr] = Punctuator($substr);
        if ($bang === '!') {
            $type['nullable'] = false;
            return [$type, $substr];
        }
    }

    [$list, $substr] = ListType($string);
    if ($list !== null) {
        [$bang, $substr] = Punctuator($substr);
        if ($bang === '!') {
            $list['nullable'] = false;
            return [$list, $substr];
        }
    }

    return [null, $string];
}
