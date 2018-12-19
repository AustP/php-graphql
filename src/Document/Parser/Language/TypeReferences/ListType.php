<?php

namespace GraphQL\Document\Parser\Language\TypeReferences;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;

// ListType : [ Type ]
function ListType($string)
{
    [$leftBracket, $substr] = Punctuator($string);
    if ($leftBracket !== '[') {
        return [null, $string];
    }

    [$type, $substr] = Type($substr);
    if ($type === null) {
        Parser::throwSyntax('Type', $substr);
    }

    [$rightBracket, $nextSubstr] = Punctuator($substr);
    if ($rightBracket !== ']') {
        Parser::throwSyntax(']', $substr);
    }

    return [['nullable' => true, 'value' => $type], $nextSubstr];
}
