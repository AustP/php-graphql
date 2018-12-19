<?php

namespace GraphQL\Document\Parser\TypeSystem\Scalars;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Directives\DirectivesConst;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\util\Keyword;

// ScalarTypeExtension : extend scalar Name Directives[Const]
function ScalarTypeExtension($string)
{
    [$extend, $substr] = Keyword($string, 'extend');
    if ($extend !== 'extend') {
        return [null, $string];
    }

    [$scalar, $substr] = Keyword($substr, 'scalar');
    if ($scalar !== 'scalar') {
        return [null, $string];
    }

    [$name, $substr] = Name($substr);
    if ($name === null) {
        Parser::throwSyntax('Name', $substr);
    }

    [$directives, $substr] = DirectivesConst($substr, 'SCALAR');
    if ($directives === null) {
        Parser::throwSyntax('DirectivesConst', $substr);
    }

    return [
        ['directives' => $directives, 'name' => $name, 'type' => 'scalar'],
        $substr
    ];
}
