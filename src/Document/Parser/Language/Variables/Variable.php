<?php

namespace GraphQL\Document\Parser\Language\Variables;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;

// Variable : $ Name
function Variable($string)
{
    [$dollar, $substr] = Punctuator($string);
    if ($dollar !== '$') {
        return [null, $string];
    }

    [$name, $substr] = Name($substr);
    if ($name === null) {
        Parser::throwSyntax('Name', $substr);
    }

    return [['type' => 'Variable', 'value' => $name], $substr];
}
