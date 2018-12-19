<?php

namespace GraphQL\Document\Parser\TypeSystem;

use GraphQL\Document\Parser;

/*
TypeSystemDefinition :
- SchemaDefinition
- TypeDefinition
- DirectiveDefinition
 */
function TypeSystemDefinition($string)
{
    [$schema, $substr] = Schema\SchemaDefinition($string);
    if ($schema !== null) {
        return [$schema, $substr];
    }

    [$type, $substr] = Types\TypeDefinition($string);
    if ($type !== null) {
        return [$type, $substr];
    }

    [$directive, $substr] = Directives\DirectiveDefinition($string);
    if ($directive !== null) {
        return [$directive, $substr];
    }

    return [null, $string];
}
