<?php

namespace GraphQL\Document\Parser\TypeSystem\Types;

use function GraphQL\Document\Parser\TypeSystem\Enums\EnumTypeExtension;
use function GraphQL\Document\Parser\TypeSystem\InputObjects\InputObjectTypeExtension;
use function GraphQL\Document\Parser\TypeSystem\Interfaces\InterfaceTypeExtension;
use function GraphQL\Document\Parser\TypeSystem\Objects\ObjectTypeExtension;
use function GraphQL\Document\Parser\TypeSystem\Scalars\ScalarTypeExtension;
use function GraphQL\Document\Parser\TypeSystem\Unions\UnionTypeExtension;

/*
TypeExtension :
- ScalarTypeExtension
- ObjectTypeExtension
- InterfaceTypeExtension
- UnionTypeExtension
- EnumTypeExtension
- InputObjectTypeExtension
 */
function TypeExtension($string)
{
    [$extension, $substr] = ScalarTypeExtension($string);
    if ($extension !== null) {
        return [$extension, $substr];
    }

    [$extension, $substr] = ObjectTypeExtension($string);
    if ($extension !== null) {
        return [$extension, $substr];
    }

    [$extension, $substr] = InterfaceTypeExtension($string);
    if ($extension !== null) {
        return [$extension, $substr];
    }

    [$extension, $substr] = UnionTypeExtension($string);
    if ($extension !== null) {
        return [$extension, $substr];
    }

    [$extension, $substr] = EnumTypeExtension($string);
    if ($extension !== null) {
        return [$extension, $substr];
    }

    [$extension, $substr] = InputObjectTypeExtension($string);
    if ($extension !== null) {
        return [$extension, $substr];
    }

    return [null, $string];
}
