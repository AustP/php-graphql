<?php

namespace GraphQL\Document\Parser\TypeSystem;

use function GraphQL\Document\Parser\TypeSystem\Schema\SchemaExtension;
use function GraphQL\Document\Parser\TypeSystem\Types\TypeExtension;

/*
TypeSystemExtension :
- SchemaExtension
- TypeExtension
 */
function TypeSystemExtension($string)
{
    [$extension, $substr] = SchemaExtension($string);
    if ($extension !== null) {
        return [$extension, $substr];
    }

    [$extension, $substr] = TypeExtension($string);
    if ($extension !== null) {
        return [$extension, $substr];
    }

    return [null, $string];
}
