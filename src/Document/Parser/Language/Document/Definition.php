<?php

namespace GraphQL\Document\Parser\Language\Document;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\TypeSystem\TypeSystemDefinition;
use function GraphQL\Document\Parser\TypeSystem\TypeSystemExtension;

/*
Definition :
- ExecutableDefinition
- TypeSystemDefinition
- TypeSystemExtension
 */
function Definition($string)
{
    [$definition, $substr] = ExecutableDefinition($string);
    if ($definition !== null) {
        $definition['__Definition'] = 'ExecutableDefinition';
        return [$definition, $substr];
    }

    [$definition, $substr] = TypeSystemDefinition($string);
    if ($definition !== null) {
        $definition['__Definition'] = 'TypeSystemDefinition';
        return [$definition, $substr];
    }

    [$definition, $substr] = TypeSystemExtension($string);
    if ($definition !== null) {
        $definition['__Definition'] = 'TypeSystemExtension';
        return [$definition, $substr];
    }

    Parser::throwSyntax('Definition', $string);
}
