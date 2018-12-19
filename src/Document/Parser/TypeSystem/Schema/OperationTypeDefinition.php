<?php

namespace GraphQL\Document\Parser\TypeSystem\Schema;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Operations\OperationType;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;
use function GraphQL\Document\Parser\Language\TypeReferences\NamedType;

// OperationTypeDefinition : OperationType : NamedType
function OperationTypeDefinition($string)
{
    [$operationType, $substr] = OperationType($string);
    if ($operationType === null) {
        return [null, $string];
    }

    [$colon, $nextSubstr] = Punctuator($substr);
    if ($colon !== ':') {
        Parser::throwSyntax(':', $substr);
    }

    [$type, $substr] = NamedType($nextSubstr);
    if ($type === null) {
        Parser::throwSyntax('NamedType', $substr);
    }

    return [['type' => $operationType, 'value' => $type['value']], $substr];
}
