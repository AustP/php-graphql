<?php

namespace GraphQL\Document\Parser\TypeSystem\Types;

use function GraphQL\Document\Parser\TypeSystem\Enums\EnumTypeDefinition;
use function GraphQL\Document\Parser\TypeSystem\InputObjects\InputObjectTypeDefinition;
use function GraphQL\Document\Parser\TypeSystem\Interfaces\InterfaceTypeDefinition;
use function GraphQL\Document\Parser\TypeSystem\Objects\ObjectTypeDefinition;
use function GraphQL\Document\Parser\TypeSystem\Scalars\ScalarTypeDefinition;
use function GraphQL\Document\Parser\TypeSystem\Unions\UnionTypeDefinition;

/*
TypeDefinition :
- ScalarTypeDefinition
- ObjectTypeDefinition
- InterfaceTypeDefinition
- UnionTypeDefinition
- EnumTypeDefinition
- InputObjectTypeDefinition
 */
function TypeDefinition($string)
{
    [$definition, $substr] = ScalarTypeDefinition($string);
    if ($definition !== null) {
        return [$definition, $substr];
    }

    [$definition, $substr] = ObjectTypeDefinition($string);
    if ($definition !== null) {
        return [$definition, $substr];
    }

    [$definition, $substr] = InterfaceTypeDefinition($string);
    if ($definition !== null) {
        return [$definition, $substr];
    }

    [$definition, $substr] = UnionTypeDefinition($string);
    if ($definition !== null) {
        return [$definition, $substr];
    }

    [$definition, $substr] = EnumTypeDefinition($string);
    if ($definition !== null) {
        return [$definition, $substr];
    }

    [$definition, $substr] = InputObjectTypeDefinition($string);
    if ($definition !== null) {
        return [$definition, $substr];
    }

    return [null, $string];
}
