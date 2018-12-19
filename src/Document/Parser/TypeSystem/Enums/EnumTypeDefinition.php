<?php

namespace GraphQL\Document\Parser\TypeSystem\Enums;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Directives\DirectivesConst;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\TypeSystem\Descriptions\Description;
use function GraphQL\Document\Parser\util\Keyword;

// EnumTypeDefinition : Description? enum Name Directives[Const]?
//   EnumValuesDefinition?
function EnumTypeDefinition($string)
{
    [$description, $substr] = Description($string);
    [$enum, $substr] = Keyword($substr, 'enum');
    if ($enum !== 'enum') {
        return [null, $string];
    }

    [$name, $substr] = Name($substr);
    if ($name === null) {
        Parser::throwSyntax('Name', $substr);
    }

    [$directives, $substr] = DirectivesConst($substr, 'ENUM');
    [$values, $nextSubstr] = EnumValuesDefinition($substr);
    if ($values === null) {
        Parser::throwInvalid(
            "`enum $name` must define one or more enum value.",
            $substr
        );
    }

    $definition = [];

    if ($description !== null) {
        $definition['description'] = $description;
    }

    if ($directives !== null) {
        $definition['directives'] = $directives;
    }

    $definition['name'] = $name;
    $definition['type'] = 'enum';

    if ($values !== null) {
        $definition['values'] = $values;
    }

    return [$definition, $nextSubstr];
}
