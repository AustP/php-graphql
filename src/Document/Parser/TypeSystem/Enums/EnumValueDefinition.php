<?php

namespace GraphQL\Document\Parser\TypeSystem\Enums;

use function GraphQL\Document\Parser\Language\Directives\DirectivesConst;
use function GraphQL\Document\Parser\Language\InputValues\EnumValue;
use function GraphQL\Document\Parser\TypeSystem\Descriptions\Description;

// EnumValueDefinition : Description? EnumValue Directives[Const]?
function EnumValueDefinition($string)
{
    [$description, $substr] = Description($string);
    [$type, $substr] = EnumValue($substr);
    if ($type === null) {
        return [null, $string];
    }

    [$directives, $substr] = DirectivesConst($substr, 'ENUM_VALUE');

    $definition = [];

    if ($description !== null) {
        $definition['description'] = $description;
    }

    if ($directives !== null) {
        $definition['directives'] = $directives;
    }

    $definition['name'] = $type['value'];
    return [$definition, $substr];
}
