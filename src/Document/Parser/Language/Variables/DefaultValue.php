<?php

namespace GraphQL\Document\Parser\Language\Variables;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\InputValues\ValueConst;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;

// DefaultValue : = Value[Const]
function DefaultValue($string)
{
    [$equals, $substr] = Punctuator($string);
    if ($equals !== '=') {
        return [null, $string];
    }

    [$value, $substr] = ValueConst($substr);
    if ($value === null) {
        Parser::throwSyntax('ValueConst', $substr);
    }

    return [$value, $substr];
}
